<?php
namespace PharIo\FileSystem;

class Filename {

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name) {
        $this->ensureString($name);
        $this->name = $name;
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     */
    private function ensureString($name) {
        if (!is_string($name)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'String expected but "%s" received',
                    is_object($name) ? get_class($name) : gettype($name)
                )
            );
        }
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->asString();
    }

    /**
     * @return string
     */
    public function asString() {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function exists() {
        return file_exists($this->name);
    }

    /**
     * @return bool
     */
    public function isExecutable() {
        return is_executable($this->name);
    }

    /**
     * @return File
     */
    public function read() {
        if (!$this->exists()) {
            throw new \RuntimeException('Cannot read - File does not (yet?) exist');
        }
        return new File($this, file_get_contents($this->asString()));
    }

    /**
     * @return Filename
     */
    public function withAbsolutePath() {
        return $this->getDirectory()->withAbsolutePath()->file($this->getBasename());
    }

    /**
     * @return Directory
     */
    public function getDirectory() {
        return new Directory(dirname($this));
    }

    /**
     * @param Directory $directory
     *
     * @return Filename
     */
    public function getRelativePathTo(Directory $directory) {
        return new Filename($this->getDirectory()->getRelativePathTo($directory) . $this->getBasename());
    }

    /**
     * @param string $content
     *
     * @return int
     */
    public function putContent($content) {
        return file_put_contents($this->asString(), $content);
    }

    /**
     * @return bool
     */
    public function delete() {
        return unlink($this->asString());
    }

    /**
     * @param \DateTimeImmutable $date
     * @return bool
     */
    public function isOlderThan(\DateTimeImmutable $date) {
        return $this->getLastModified()->isOlderThan($date);
    }

    /**
     * @return Filename
     */
    public function withoutExtension() {
        $pathinfo = pathinfo($this->asString());

        return new Filename($pathinfo['dirname'] . '/' . $pathinfo['filename']);
    }

    /**
     * @return LastModifiedDate
     * @throws FilenameException
     */
    private function getLastModified() {
        return LastModifiedDate::fromTimestamp(filemtime($this->asString()));
    }

    /**
     * @return string
     */
    private function getBasename() {
        return pathinfo($this, PATHINFO_BASENAME);
    }

}
