<?php if (!empty($datas)) { ?>
    <div class="exception-var debug-error-vars-box">
        <h2>Exception Datas</h2>
        <?php foreach ((array) $datas as $label => $value) { ?>
            <table>
                <?php if (empty($value)) { ?>
                    <caption><?php echo $label; ?><small>empty</small></caption>
                <?php } else { ?>
                    <caption><?php echo $label; ?></caption>
                    <tbody>
                        <?php foreach ((array) $value as $key => $val) { ?>
                            <tr>
                                <td><?php echo htmlentities($key); ?></td>
                                <td><?php echo_value($val); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                <?php } ?>
            </table>
        <?php } ?>
    </div>
<?php } ?>

<?php if (!empty($tables)) { ?>
    <div class="exception-var debug-error-vars-box">
        <h2>Environment Variables</h2>
        <?php foreach ((array) $tables as $label => $value) { ?>
            <table>
                <?php if (empty($value)) { ?>
                    <caption><?php echo $label; ?><small>empty</small></caption>
                <?php } else { ?>
                    <caption><?php echo $label; ?></caption>
                    <tbody>
                        <?php foreach ((array) $value as $key => $val) { ?>
                            <tr>
                                <td><?php echo htmlentities($key); ?></td>
                                <td><?php echo_value($val); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                <?php } ?>
            </table>
        <?php } ?>
    </div>
<?php } ?>