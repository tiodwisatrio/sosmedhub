<?php

namespace Modules\SocialAccount\Exceptions;

use RuntimeException;

/**
 * Facebook menolak token Page (kedaluwarsa, dicabut, atau izin ditarik).
 */
class FacebookAuthException extends RuntimeException {}
