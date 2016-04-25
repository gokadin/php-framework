<?php

namespace Library\Session;

if (session_status() == PHP_SESSION_NONE && env('APP_ENV') != 'testing')
{
    session_start();
}

class Session
{
    const ERRORS_KEY = 'errors';
    const SHOULD_CLEAR_ERRORS_KEY = 'shouldClearErrors';
    const FLASH_KEY = 'flash';
    const FLASH_TYPE_KEY = 'flashType';
    const FLASH_DURATION_KEY = 'flashDuration';
    const SHOULD_CLEAR_FLASH_KEY = 'shouldClearFlash';

    private $token;

    public function __construct()
    {
        if (!$this->shouldClearErrors())
            $_SESSION[self::SHOULD_CLEAR_ERRORS_KEY] = true;
        else
            $this->clearErrors();

        if ($this->shouldClearFlash())
        {
            $this->clearFlash();
        }
        else
        {
            $_SESSION[self::SHOULD_CLEAR_FLASH_KEY] = true;
        }

        $_SESSION['token'] = $this->generateToken(); // TODO: move from here
    }

    public function set($var, $value)
    {
        $_SESSION[$var] = $value;
    }
    
    public function get($var)
    {
        if ($this->exists($var)) 
            return $_SESSION[$var];
    }

    public function exists($var)
    {
        return isset($_SESSION[$var]);
    }

    public function remove($key)
    {
        if (isset($_SESSION[$key]))
            unset($_SESSION[$key]);
    }

    public function generateToken()
    {
        if (isset($this->token))
            return $this->token;

        $this->token = md5('G2s92!dK2!185fr0?Se0');
        return $this->token;
    }

    public function setErrors($errors)
    {
        $_SESSION[self::ERRORS_KEY] = $errors;
        $_SESSION[self::SHOULD_CLEAR_ERRORS_KEY] = false;
    }

    public function setFlash($string, $type = "success", $duration = 0)
    {
        if ($type != 'success' && $type != 'error')
            $type = 'success';
            
        if (!is_int($duration) || $duration < 0)
            $duration = 0;
        
        $_SESSION[self::FLASH_KEY] = $string;
        $_SESSION[self::FLASH_TYPE_KEY] = $type;
        $_SESSION[self::FLASH_DURATION_KEY] = $duration;
        $_SESSION[self::SHOULD_CLEAR_FLASH_KEY] = false;

        setcookie(self::FLASH_KEY, htmlspecialchars($string), time() + (365 * 24 * 60 * 60));
        setcookie(self::FLASH_TYPE_KEY, htmlspecialchars($type), time() + (365 * 24 * 60 * 60));
    }

    public function getErrors()
    {
        if (isset($_SESSION[self::ERRORS_KEY]))
            return $_SESSION[self::ERRORS_KEY];

        return null;
    }

    public function getFlash()
    {
        if (isset($_SESSION[self::FLASH_KEY]))
            return $_SESSION[self::FLASH_KEY];

        return null;
    }
    
    public function getFlashType()
    {
        if (isset($_SESSION[self::FLASH_TYPE_KEY]))
            return $_SESSION[self::FLASH_TYPE_KEY];

        return null;
    }
    
    public function getFlashDuration()
    {
        if (isset($_SESSION[self::FLASH_DURATION_KEY]))
            return $_SESSION[self::FLASH_DURATION_KEY];

        return null;
    }

    public function hasErrors()
    {
        return isset($_SESSION[self::ERRORS_KEY]);
    }

    public function hasFlash()
    {
        return isset($_SESSION[self::FLASH_KEY]);
    }

    public function clearErrors()
    {
        $this->remove(self::ERRORS_KEY);
    }

    public function clearFlash()
    {
        $this->remove(self::FLASH_KEY);
        $this->remove(self::FLASH_TYPE_KEY);
        $this->remove(self::FLASH_DURATION_KEY);

        if (isset($_COOKIE[self::FLASH_KEY]))
        {
            setcookie(self::FLASH_KEY, '', time() - 1);
        }

        if (isset($_COOKIE[self::FLASH_TYPE_KEY]))
        {
            setcookie(self::FLASH_TYPE_KEY, '', time() - 1);
        }
    }

    protected function shouldClearErrors()
    {
        if (!isset($_SESSION[self::SHOULD_CLEAR_ERRORS_KEY]))
            return false;

        return $_SESSION[self::SHOULD_CLEAR_ERRORS_KEY];
    }

    protected function shouldClearFlash()
    {
        if (!isset($_SESSION[self::SHOULD_CLEAR_FLASH_KEY]))
            return false;

        return $_SESSION[self::SHOULD_CLEAR_FLASH_KEY] === true;
    }
}
