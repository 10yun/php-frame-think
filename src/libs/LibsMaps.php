<?php

namespace shiyun\libs;

class Coordinate
{
    public $x = 0;
    public $y = 0;
    /**
     * Coordinate constructor.
     * @param $lon float 经度
     * @param $lat float 纬度
     */
    public function __construct($lon, $lat)
    {
        $this->x = $lon;
        $this->y = $lat;
    }
}
define('X_PI', 3.14159265358979324 * 3000.0 / 180.0);
class CoordinateTool
{
    private static $pi = 3.14159265358979324; // 圆周率
    private static $a = 6378245.0; // WGS 长轴半径
    private static $ee = 0.00669342152296594323; // WGS 偏心率的平方
    /**
     * 将火星坐标系GCJ-02 坐标 转换成百度坐标系 BD-09 坐标
     * @param $gc_loc 火星坐标点(Class Coordinate)
     * @return $bg_loc Coordinate对象，百度地图经纬度坐标
     */
    public static function gcj_bd($gc_loc)
    {
        $x_pi = X_PI;
        $x = $gc_loc->x;
        $y = $gc_loc->y;
        $z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) + 0.000003 * cos($x * $x_pi);
        $bd_x = $z * cos($theta) + 0.0065;
        $bd_y = $z * sin($theta) + 0.006;
        $bg_loc = new Coordinate($bd_x, $bd_y);
        return $bg_loc;
    }
    /**
     * 将百度坐标系 BD-09 坐标 转换成 火星坐标系GCJ-02 坐标
     * @param $bd_loc 火星坐标点(Class Coordinate)
     *  @return $bg_loc Coordinate对象，火星坐标系经纬度坐标
     */
    public static function bd_gcj($bd_loc)
    {
        $x_pi = X_PI;
        $x = $bd_loc->x - 0.0065;
        $y = $bd_loc->y - 0.006;
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
        $gc_x = $z * cos($theta);
        $gc_y = $z * sin($theta);
        $gc_loc = new Coordinate($gc_x, $gc_y);
        return $gc_loc;
    }
    /**
     * 将国际通用坐标系WGS84坐标 转换成 火星坐标系GCJ-02 坐标
     * @param $wgs_loc WGS84坐标点(Class Coordinate)
     *  @return $bg_loc Coordinate对象，火星坐标系经纬度坐标
     */
    public static function wgs_gcj($wgs_loc)
    {
        $wgs_lon = $wgs_loc->x;
        $wgs_lat = $wgs_loc->y;

        if (self::outOfChina($wgs_lon, $wgs_lat)) {
            return (new Coordinate($wgs_lon, $wgs_lat));
        }
        $x_pi = X_PI;
        $dLat = self::transformLat($wgs_lon - 105.0, $wgs_lat - 35.0);
        $dLon = self::transformLon($wgs_lon - 105.0, $wgs_lat - 35.0);
        $radLat = $wgs_lat / 180.0 * $x_pi;
        $magic = sin($radLat);
        $magic = 1 - self::$ee * $magic * $magic;
        $sqrtMagic = sqrt($magic);
        $dLat = ($dLat * 180.0) / ((self::$a * (1 - self::$ee)) / ($magic * $sqrtMagic) * self::$pi);
        $dLon = ($dLon * 180.0) / (self::$a / $sqrtMagic * cos($radLat) * self::$pi);

        $mgLat = $wgs_lat + $dLat;
        $mgLon = $wgs_lon + $dLon;

        $gcj_loc = new Coordinate($mgLon, $mgLat);
        return $gcj_loc;
    }
    /**
     * 将火星坐标系GCJ-02坐标 转换成 国际通用坐标系WGS84坐标
     * @param $gcj_loc GCJ-02坐标点(Class Coordinate)
     *  @return $wgs_loc Coordinate对象，国际通用坐标系WGS84坐标
     */
    public static function gcj_wgs($gcj_loc)
    {
        $to = self::wgs_gcj($gcj_loc);
        $lon = $gcj_loc->x;
        $lat = $gcj_loc->y;
        $gcl_lon = $to->x;
        $gcl_lat = $to->y;
        $d_lat = $gcl_lat - $lat;
        $d_lon = $gcl_lon - $lon;
        return new Coordinate($lon - $d_lon, $lat - $d_lat);
    }
    private static function outOfChina($lon, $lat)
    {
        if ($lon < 72.004 || $lon > 137.8347)
            return true;
        if ($lat < 0.8293 || $lat > 55.8271)
            return true;

        return false;
    }
    private static function transformLat($x, $y)
    {
        $ret = -100.0 + 2.0 * $x + 3.0 * $y + 0.2 * $y * $y + 0.1 * $x * $y + 0.2 * sqrt(abs($x));
        $ret += (20.0 * sin(6.0 * $x * self::$pi) + 20.0 * sin(2.0 * $x * self::$pi)) * 2.0 / 3.0;
        $ret += (20.0 * sin($y * self::$pi) + 40.0 * sin($y / 3.0 * self::$pi)) * 2.0 / 3.0;
        $ret += (160.0 * sin($y / 12.0 * self::$pi) + 320 * sin($y * self::$pi / 30.0)) * 2.0 / 3.0;
        return $ret;
    }
    private static function transformLon($x, $y)
    {
        $ret = 300.0 + $x + 2.0 * $y + 0.1 * $x * $x + 0.1 * $x * $y + 0.1 * sqrt(abs($x));
        $ret += (20.0 * sin(6.0 * $x * self::$pi) + 20.0 * sin(2.0 * $x * self::$pi)) * 2.0 / 3.0;
        $ret += (20.0 * sin($x * self::$pi) + 40.0 * sin($x / 3.0 * self::$pi)) * 2.0 / 3.0;
        $ret += (150.0 * sin($x / 12.0 * self::$pi) + 300.0 * sin($x / 30.0 * self::$pi)) * 2.0 / 3.0;

        return $ret;
    }
}

/**
 * 地图操作类
 * @author ctocode-zhw
 * @version 2018-0619 22:07
 */
class LibsMaps
{
    public function huoxingToBaidu()
    {
        // 将火星坐标系GCJ-02 坐标 转换成百度坐标系 BD-09 坐标
        $coordinate = new Coordinate(113.3998, 34.5443); // 吃货天堂的坐标
        $coord = CoordinateTool::gcj_bd($coordinate);
    }
    public function BaiduToHuoxing()
    {

        // 将百度坐标系 BD-09 坐标 转换成 火星坐标系GCJ-02 坐标
        $coordinate = new Coordinate(113.3996, 34.543);
        $coord = CoordinateTool::bd_gcj($coordinate);
    }
    public function guojiToHuoxing()
    { // 将国际通用坐标系WGS84坐标 转换成 火星坐标系GCJ-02 坐标
        $coordinate = new Coordinate(113.3924, 34.54036);
        $coord = CoordinateTool::wgs_gcj($coordinate);
    }
    public function huoxingToGuoji()
    { // 将火星坐标系GCJ-02坐标 转换成 国际通用坐标系WGS84坐标
        $coordinate = new Coordinate(113.3998, 34.5443);
        $coord = CoordinateTool::gcj_wgs($coordinate);
    }
    /**
     *  计算两组经纬度坐标 之间的距离
     *   @param $lng1 $lng2 经度；
     *   @param $lat1 $lat2 纬度；
     *   @param $len_type （1:米 2:千米);
     *   return $s 距离（默认单位：米）
     */
    function mapGetDistance($lng1, $lat1, $lng2, $lat2, $len_type = 1, $decimal = 0)
    {
        $radLat1 = $lat1 * PI() / 180.0; // PI()圆周率
        $radLat2 = $lat2 * PI() / 180.0;
        $a = $radLat1 - $radLat2;
        $b = ($lng1 * PI() / 180.0) - ($lng2 * PI() / 180.0);
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $s = $s * 6378.137;
        $s = round($s * 1000);
        if ($len_type == 2) {
            $s /= 1000;
        }
        return round($s, $decimal);
    }
    /**
     * 根据两点间的经纬度计算距离
     * @param $lng1
     * @param $lat1
     * @param $lng2
     * @param $lat2
     * @return int
     */
    function mapGetDistance2($lng1, $lat1, $lng2, $lat2)
    {
        $lng1 = (float) $lng1;
        $lat1 = (float) $lat1;
        $lng2 = (float) $lng2;
        $lat2 = (float) $lat2;
        // 将角度转为狐度
        $radLat1 = deg2rad($lat1); // deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
        return round($s, 0);
    }

    /**
     * 百度坐标转腾讯坐标
     * @param array $wsql
     */
    public function baiduToQq(&$wsql = array())
    {
        if (!empty($wsql['map_longitude']) && !empty($wsql['map_latitude'])) {
            $map = $this->map_conversion(array(
                0 => array(
                    "lng" => $wsql['map_longitude'],
                    "lat" => $wsql['map_latitude']
                )
            ));
            $map = json_decode($map, true);
            $wsql['map_latitude'] = $map['result'][0]['y'];
            $wsql['map_longitude'] = $map['result'][0]['x'];
        }
    }
    protected function map_conversion($map = array(), $from = 5, $to = 3)
    {
        $url = "http://api.map.baidu.com/geoconv/v1/?";
        $url .= "ak=dT85sFHzWrGyHVGvZkUsHxLrdSuKlGuA&from={$from}&to={$to}";
        $coords = "";
        $num = count($map) - 1;
        foreach ($map as $key => $val) {
            $coords .= $val['lng'] . "," . $val['lat'];
            if ($num != $key) {
                $coords .= ";";
            }
        }
        $url .= "&coords=" . $coords;
        return ctoHttpCurl($url);
    }
}
