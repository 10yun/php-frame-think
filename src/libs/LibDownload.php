<?php

namespace shiyun\libs;

class LibDownload
{
    // get请求获取body体
    function curl_get_with_body($url, $range)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => array(
                "Range: bytes={$range}"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }
    //get请求
    function curl_get_with_head($url, $header)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "HEAD",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 2,
            //CURLINFO_HEADER_OUT => TRUE,    //获取请求头
            CURLOPT_HEADER => true, //获取响应头
            CURLOPT_NOBODY => true, //不需要响应正文
            CURLOPT_HTTPHEADER => $header,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        $responseHeader = explode("\r\n", $response);
        $totalLength = $eTag = '';
        foreach ($responseHeader as $response) {
            if ($response) {
                //获取文件总长度
                if (str_contains($response, 'Content-Range:', 0)) {
                    $totalLength = explode(':', $response);
                    $totalLength = array_pop($totalLength);
                    $totalLength = explode('/', $totalLength);
                    $totalLength = array_pop($totalLength);
                }
                //获取文件etag
                if (str_contains($response, 'ETag:', 0)) {
                    $eTag = explode(':', $response);
                    $eTag = array_pop($eTag);
                    $eTag = trim(str_replace('"', '', $eTag));
                }
            }
        }

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return ['length' => $totalLength, 'etag' => $eTag];
        }
    }

    function download_file($file_url, $file_path, $rname, $ext)
    {
        //    $file = "http://public.geek-in.net/clouddisk/NYsrTpPRjz.mp4";
        $range = "0-1";
        $response = $this->curl_get_with_head($file_url, array(
            "Range: bytes={$range}"
        ));
        $eTag = $response['etag'];
        $length = $response['length'];
        dd($length);
        if (file_exists($file_path . $rname . '.' . $ext)) {
            if (filesize($file_path . $rname . '.' . $ext) != $length) { //大小不一样重新下载
                try {
                    unlink($file_path . $rname . '.' . $ext);
                } catch (\Exception $exception) {
                    echo "删除文件失败：{$file_path}{$rname}.{$ext}" . PHP_EOL;
                }
                $chunkCount = 100;
                $chunkCount = $length / 10000000;
                $step = ceil($length / $chunkCount);
                // echo 11111;
                $fp = fopen("{$file_path}{$rname}.{$ext}", 'w+');
                for ($i = 0; $i < $chunkCount; $i++) {
                    $start = $i * $step;
                    $end = (($i + 1) * $step) - 1;
                    if ($end > $length) {
                        $end = $length;
                    }
                    $range = $start . '-' . $end;
                    $con = $this->curl_get_with_body($file_url, $range);
                    // file_put_contents("{$file_path}{$rname}.{$ext}", $con, FILE_APPEND);

                    fwrite($fp, $con);
                    echo ("总大小{$length},共计{$chunkCount}片，第{$i}片下载完成, range:" . $range . PHP_EOL);
                }
                echo "下载完成，总大小：{$length},";
                fclose($fp);
            }
        }
    }
    /**
     * 其他测试下载
     */
    function xxx()
    {


        // $fp_input = fopen($url_new, 'r');
        // while ($content = fread($fp_input, 1024)) { //一次读1024字节
        //     file_put_contents($filePath, $content);
        // }

        // ==== 2 
        // $fp_output = fopen($filePath, 'w');
        // $ch = curl_init($url_new);
        // curl_setopt($ch, CURLOPT_FILE, $fp_output);
        // curl_exec($ch);
        // curl_close($ch);

        // ==== 3
        // $curl2 = curl_init($url_new);
        // // curl_setopt($curl2, CURLOPT_URL, $url_new); //登陆后要从哪个页面获取信息
        // // curl_setopt($curl2, CURLOPT_HEADER, false);
        // curl_setopt($curl2, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($curl2, CURLOPT_SSL_VERIFYPEER, 0); //post提交数据
        // curl_setopt($curl2, CURLOPT_SSL_VERIFYHOST, 0); //post提交数据
        // $content = curl_exec($curl2);
        // file_put_contents($filePath, $content);
        // if (curl_exec($curl2) === false) {
        //     echo 'Curl error: ' . curl_error($curl2);
        // } else {
        //     echo '操作完成没有任何错误';
        // }
        // curl_close($curl2);
    }
    /**
     * 获取远程文件内容
     * @param $url 文件http地址
     */

    function fopen_url($url)
    {
        $file_content = "";
        if (function_exists('file_get_contents')) {
            $file_content = @file_get_contents($url);
        } elseif (ini_get('allow_url_fopen') && ($file = @fopen($url, 'rb'))) {
            $i = 0;
            while (!feof($file) && $i++ < 1000) {
                $file_content .= strtolower(fread($file, 4096));
            }
            fclose($file);
        } elseif (function_exists('curl_init')) {
            $curl_handle = curl_init();
            curl_setopt($curl_handle, CURLOPT_URL, $url);
            curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl_handle, CURLOPT_FAILONERROR, 1);
            curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Trackback Spam Check');
            $file_content = curl_exec($curl_handle);
            curl_close($curl_handle);
        } else {
            $file_content = '';
        }
        return $file_content;
    }
}
