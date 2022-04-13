<?php

class TagService extends ServiceAbstract
{
    /**
     * Retrieve set of tags with count of ucurrences
     * @return array of Tag
     */
    public function getTagsCloud() {
        $tagsCloudMapper = new TagsCloudMapper();
        return $tagsCloudMapper->getTagsCloudList();
    }


    public function deleteVideoTags(Video $video)
    {
        // select all tags of video

        $tagMapper = $this->_getMapper();
        $tagMapper->unlinkTagsFromVideoId($video->videoId);
    }
    
    /**
     * Retrieve instance of Comment mapper
     * @return TagMapper Mapper is returned
     */
    protected function _getMapper()
    {
        return new TagMapper();
    }
}