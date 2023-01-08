 <?php if (!empty($trace['source'])) { ?>

     <div class="source-code debug-error-code-box">
         <div class="debug-error-code__file">
             <strong><?php echo parse_code_class($trace['file']); ?></strong>
             <span class="debug-error-code__line"> line <?php echo $trace['line']; ?></span>
             <!-- <h2><?php echo "#{$index} [{$trace['code']}]" ?></h2> -->
         </div>
         <pre class="prettyprint lang-php"><ol start="<?php echo $trace['source']['first']; ?>"><?php foreach ((array) $trace['source']['source'] as $key => $value) {
                                                                                                    $lineClass = "{$index}-";
                                                                                                    $lineClass .= $key + $trace['source']['first'];
                                                                                                    $lineClass .= $trace['line'] === $key + $trace['source']['first'] ? ' line-error' : '';
                                                                                                ?><li class="line-<?php echo $lineClass; ?>"><code><?php echo htmlentities($value); ?></code></li><?php } ?></ol></pre>
     </div>
 <?php } ?>