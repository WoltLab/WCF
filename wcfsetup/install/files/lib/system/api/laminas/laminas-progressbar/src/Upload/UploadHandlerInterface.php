<?php

namespace Laminas\ProgressBar\Upload;

/**
 * Interface for Upload Progress Handlers
 */
interface UploadHandlerInterface
{
    /**
     * @param  string $id
     * @return array
     */
    public function getProgress($id);
}
