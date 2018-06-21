<?php
namespace Application\Files;

/**
 * Interface FilesServiceInterface
 * @author Alejandro Celaya Alastrué
 * @link http://www.wonnova.com
 */
interface FilesServiceInterface
{
    const CODE_SUCCESS = 'success';
    const CODE_ERROR = 'error';

    /**
     * @return \SplFileInfo[]
     */
    public function getFiles();

    /**
     * @param array $files
     * @return string
     */
    public function persistFiles(array $files);

    /**
     * @param array $files
     * @return string
     */
    public function addValidator(\Zend\Validator\AbstractValidator $files);

    /**
     * @return array
     */
    public function getErrorMessages();

    /**
     * Adds path to the base path
     * @param string $basePath
     */
    public function addPath($path);

    /**
     * @return string
     */
    public function getFullPath();
}
