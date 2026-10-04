<?php

namespace Modules\SocialAccount\Exceptions;

use RuntimeException;

/**
 * Instagram menolak token (kedaluwarsa, dicabut, atau izin ditarik).
 */
class InstagramAuthException extends RuntimeException {}
