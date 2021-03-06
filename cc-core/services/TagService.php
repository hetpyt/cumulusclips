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
        $tagMapper = $this->_getMapper();
        // select all tags of video
        $videoTags = $tagMapper->getTagsByVideoId($video->videoId);
        $tagMapper->unlinkTagsFromVideoId($video->videoId);
        // check unused tags, and delete
        foreach($videoTags as $tagObj) {
            $linksCount = $tagMapper->getTagLinksCount($tagObj->tagId);
            if ($linksCount == 0) {
                $tagMapper->delete($tagObj->tagId);
            }
        }
    }
    
    /**
     * Retrieve videos by tagId.
     *
     * @param int $tagId tag ID
     */
    public function getVideosByTagId($tagId, $start=null, $count=null) {
        $tagMapper = $this->_getMapper();
        $videoIds = $tagMapper->getVideoIdsByTagId($tagId, $start, $count);
        $videoMapper = new VideoMapper();
        return $videoMapper->getVideosFromList($videoIds);
    }

    /**
     * Retrieve count of videos by tagId.
     *
     * @param int $tagId tag ID
     */
    public function getCountVideosByTagId($tagId) {
        $tagMapper = $this->_getMapper();
        $videoIds = $tagMapper->getVideoIdsByTagId($tagId);
        return count($videoIds);
    }
    

    /**
     * Retrieve instance of Tag mapper
     * @return TagMapper Mapper is returned
     */
    protected function _getMapper()
    {
        return new TagMapper();
    }
}