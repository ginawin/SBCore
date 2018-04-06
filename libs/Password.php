<?php
/**
 * Password library file.
 * @author LVP [levanphu.info] <vanphupc50@gmail.com>
 * @copyright 2018 LP Group.
 * @since 1.0
 */

class Password
{
    private static $__instance = null;

    /**
     * Autoloader implement. @see VSInterfaceLibrary
     * @author HackerPro536 (Le Van Phu)
     */
    public function autoLoader() {
    }

    /**
     * GetInstance
     * @return $__instance
     */
    public static function getInstance()
    {
        if (null === self::$__instance) {
            self::$__instance = new self();
        }

        return self::$__instance;
    }

    public function __construct()
    {

    }

    /**
     * Validate if password string is valid.
     * @param  string $password
     * @return boolean
     */
    public static function validate($password = "") {
        return preg_match('~[A-Z]~', $password)
            && preg_match('~[a-z]~', $password)
            && preg_match('~\d~', $password);
    }

    /**
     * Create a password hash.
     * @param  string $password - The user's password.
     * @return string           - Hash password.
     * @see http://php.net/manual/en/function.password-hash.php
     */
    public static function hash($password)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        return $hash;
    }

    /**
     * Verify password.
     * @param  string $password - Input password to verfiy.
     * @param  string $hash     - Encryted password using self::hash().
     * @return boolean          - TRUE if match, otherwise FALSE.
     * @see http://php.net/manual/en/function.password-verify.php
     */
    public static function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }
    /**
     * Create a password hash.
     * @param  string $password - The user's password.
     * @return string           - Md5 password.
     */
    public static function md5($password)
    {
        $hash = '$2y$10$' . md5($password);
        return $hash;
    }
}
