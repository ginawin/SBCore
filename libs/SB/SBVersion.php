<?php
/**
 * Class Version
 * @category Library
 * @package System
 * @author LP (Le Van Phu) <vanphupc50@gmail.com>
 * @copyright 2004-2016 SB Group
 * @version 1.0
 */

class SBVersion {
    private $__major = 0;
    private $__minor = 0;
    private $__maintenance = 0;
    private $__build = 0;

    public function build() {
        return $this->__build;
    }

    public function maintenance() {
        return $this->__maintenance;
    }

    public function minor() {
        return $this->__minor;
    }

    public function major() {
        return $this->__major;
    }

    /**
     * Check is this verion is later than other.
     * @param  Version $version
     * @return boolean
     */
    public function isLater(SBVersion $version) {
        if($this->major() < $version->major()) return false;
        if($this->minor() < $version->minor()) return false;
        if($this->maintenance() < $version->maintenance()) return false;
        if($this->build() < $version->build()) return false;

        return true;
    }
    /**
     * Check if this version is earlier than other.
     * @param  Version $version
     * @return boolean
     */
    public function isEarlier(SBVersion $version) {
        if($this->major() > $version->major()) return false;
        if($this->minor() > $version->minor()) return false;
        if($this->maintenance() > $version->maintenance()) return false;
        if($this->build() > $version->build()) return false;

        return true;
    }

    private function __construct($versionString = "") {
        if(SBSystem::isEmpty($versionString)) return $this;

        $sequences = SBString::explode($versionString, ".");
        $this->__major = SBNumber::parseInt($sequences[0]);
        if (SBArray::has(1, $sequences)) $this->__minor = SBNumber::parseInt($sequences[1]);
        if (SBArray::has(2, $sequences)) $this->__maintenance = SBNumber::parseInt($sequences[2]);
        if (SBArray::has(3, $sequences)) $this->__build = SBNumber::parseInt($sequences[3]);

        return $this;
    }

    /**
     * Create version from a version string.
     * @param  string $versionString
     * @return Version
     */
    public static function create($versionString = "") {
        return new self($versionString);
    }

    /**
     * Return curent PHP version of Webservice.
     */
    public static function curentVersion()
    {
        return PHP_VERSION;
    }
}
