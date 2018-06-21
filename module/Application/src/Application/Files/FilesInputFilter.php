<?php
namespace Application\Files;

use Zend\Filter\File\RenameUpload;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilter;
use Zend\Validator\File\Size;
use Zend\Validator\File\MimeType;

/**
 * Class FilesInputFilter
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class FilesInputFilter extends InputFilter
{
    const FILE = 'file';

    protected $input;

    public function __construct(FilesOptions $options)
    {
        $this->input = new FileInput(self::FILE);
        $this->input->getValidatorChain()->attach(new Size(['max' => $options->getMaxSize()]));
        $this->input->getValidatorChain()->attach(new MimeType($options->getMimeGroups()));
        $this->input->getFilterChain()->attach(new RenameUpload([
            'overwrite'         => false,
            'use_upload_name'   => true,
            'randomize'         => true,
            'target'            => $options->getBasePath()
        ]));

        $this->add($this->input);
    }

    /**
     * @return FileInput
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param FileInput $input
     */
    public function setInput(FileInput $input)
    {
        $this->input = $input;
    }
}
