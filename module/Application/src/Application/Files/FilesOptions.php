<?php
namespace Application\Files;


use Symfony\Component\Config\Definition\Exception\Exception;
use Zend\Stdlib\AbstractOptions;

class FilesOptions extends AbstractOptions
{
    /**
     * @var string
     */
    protected $basePath = '';
    /**
     * @var string
     */
    protected $maxSize = '1536MB';
    /**
     * @var array
     */
    protected $mimeGroups = array();

    protected $isAws = false;

    public function __construct(array $options = [])
    {
        if (!isset($options['base_path'])) {
            $this->setBasePath('./public');
        }
        parent::__construct($options);
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath .'/files';
    }

    /**
     * @param string $basePath
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setBasePath($basePath)
    {
//        if ($this->isAws) {
//            $basePath = str_replace("public", "", $basePath);
//            $basePath = str_replace("./", "", $basePath);
//            $basePath = 's3://';
//        }

        if (! is_dir($basePath)) {
            try {
                mkdir($basePath, 0777, true);
            } catch (Exception $e) {
                throw new \InvalidArgumentException('Provided base path is not a valid directory');
            }
        }
        $this->basePath = $basePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getMaxSize()
    {
        return $this->maxSize;
    }

    /**
     * @param string $maxSize
     * @return $this
     */
    public function setMaxSize($maxSize)
    {
        $this->maxSize = $maxSize;
        return $this;
    }

    /**
     * @return array
     */
    public function getMimeGroups() {
        return $this->mimeGroups;
    }

    /**
     * @param array mimeGroups
     * @return $this
     */
    public function setMimeGroups($mimeGroups)
    {
        $this->mimeGroups = $mimeGroups;
        return $this;
    }
}