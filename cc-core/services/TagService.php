<?php

class TagService extends ServiceAbstract
{
    /**
     * Delete a tag
     * @param Tag $tag Instance of tag to be deleted
     * @return void Tag is deleted from system
     */
    public function delete(Tag $tag)
    {
        $tagMapper = $this->_getMapper();
        $tagMapper->delete($tag->tagId);
    }

    public function deleteVideoTags(Video $video)
    {
        $tagMapper = $this->_getMapper();
        $tagMapper->deleteVideoTags($video->videoId);
    }
    
    /**
     * Retrieve subset of a video's tags
     * @param Video $video Video for which to retrieve tags
     * @return array Returns list of tags
     */
    public function getVideoTags(Video $video)
    {
        $tagMapper = $this->_getMapper();
        return $tagMapper->getVideoTagsById($video->videoId);
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