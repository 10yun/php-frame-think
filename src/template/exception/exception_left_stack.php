<div class="debug-error-stack-box">
    <div class="debug-error-stack-top" data-expand="<?php echo 0 === $index ? '1' : '0'; ?>">Call Stack</div>
    <div class="debug-error-stack-list">
        <?php $traceCount = count($trace['trace']) ?? 0; ?>
        <div class="debug-error-stack-item active">
            <span class="stack-item-index"><?php echo ($traceCount + 1); ?></span>
            <div class="stack-item-top-box">
                <div class="stack-item-top-class"><?php echo parse_exc_class($trace['name']); ?></div>
            </div>
            <div class="stack-item-file">
                <?php echo parse_code_class($trace['file']); ?> : <?php echo $trace['line']; ?>
            </div>
        </div>
        <?php foreach ((array) $trace['trace'] as $key => $value) : ?>
            <div class="debug-error-stack-item">
                <span class="stack-item-index"><?php echo ($traceCount - $key); ?></span>
                <?php if ($value['function']) : ?>
                    <div class="stack-item-top-box">
                        <div class="stack-item-top-class"><?php echo isset($value['class']) ? parse_exc_class($value['class']) : ''; ?></div>
                        <div class="stack-item-top-func">
                            <span><?php echo isset($value['type'])  ? $value['type'] : ''; ?></span>
                            <span><?php echo $value['function']; ?></span>
                        </div>
                    </div>
                    <p><?php echo isset($value['args']) ? parse_args($value['args']) : ''; ?></p>
                <?php endif; ?>
                <?php if (isset($value['file']) && isset($value['line'])) : ?>
                    <div class="stack-item-file">
                        <?php echo parse_code_class($value['file']); ?> : <?php echo $value['line']; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>