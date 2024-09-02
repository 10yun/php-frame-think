<?php

namespace shiyunQueue\exception;

use RuntimeException;

/**
 * 超过重试次数
 */
class MaxAttemptsExceededException extends RuntimeException {}
