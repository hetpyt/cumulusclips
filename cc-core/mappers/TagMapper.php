<?php

class TagMapper extends MapperAbstract
{
    public function getTagById($tagId)
    {
        return $this->getTagByCustom(array('tag_id' => $tagId));
    }

    public function getTagByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'tags WHERE ';

        $queryParams = array();
        foreach ($params as $fieldName => $value) {
            $query .= "$fieldName = :$fieldName AND ";
            $queryParams[":$fieldName"] = $value;
        }
        $query = preg_replace('/\sAND\s$/', '', $query);

        $dbResults = $db->fetchRow($query, $queryParams);
        if ($db->rowCount() > 0) {
            return $this->_map($dbResults);
        } else {
            return false;
        }
    }

    /**
     * retrieve videoIds list from db by tagId 
     * @param int  $tagId
     * @return array<int> array of videoIds
     */
    public function getVideoIdsByTagId($tagId, $start=null, $count=null) 
    {
        $db = Registry::get('db');
        //$query = 'SELECT * FROM ' . DB_PREFIX . 'tags_videos WHERE tag_id = :tagId';
        $query = "SELECT ".DB_PREFIX."tags_videos.video_id  
        FROM ".DB_PREFIX."tags_videos JOIN ".DB_PREFIX."videos ON ".DB_PREFIX."tags_videos.video_id=".DB_PREFIX."videos.video_id 
        WHERE ".DB_PREFIX."videos.status='approved' AND ".DB_PREFIX."videos.private='0'";
        if (isset($start) && isset($count)) {
            $query .= " LIMIT $start, $count";
        }
        $queryParams = array(':tagId' => $tagId);
        $dbResults = $db->fetchAll($query, $queryParams);
        if ($dbResults)
            $videoIds = Functions::arrayColumn($dbResults, 'video_id');
        else
            $videoIds = array();
        return $videoIds;
    }

    /**
     * create new Tag object 
     * @param string  $tag tag text
     * @return Tag object
     */
    public function newTag($tag)
    {
        $tagObj = new Tag();
        $tagObj->tag = $tag;
        $tagObj->tagLower = mb_strtolower($tag);
        return $tagObj;
    }

    protected function _map($dbResults)
    {
        $tag = new Tag();
        $tag->tagId = (int) $dbResults['tag_id'];
        //$tag->videoId = (int) $dbResults['video_id'];
        $tag->tag = $dbResults['tag'];
        $tag->tagLower = $dbResults['tag_lc'];
        return $tag;
    }

    public function save(Tag $tag)
    {
        $db = Registry::get('db');
        if (!empty($tag->tagId)) {
            // Update
            $query = 'UPDATE ' . DB_PREFIX . 'tags SET';
            $query .= ' tag = :tag, tag_lc = :tagLower';
            $query .= ' WHERE tag_id = :tagId';
            $bindParams = array(
                ':tagId' => $tag->tagId,
                ':tag' => $tag->tag,
                ':tagLower' => $tag->tagLower,
            );
        } else {
            // Create
            $query = 'INSERT INTO ' . DB_PREFIX . 'tags';
            $query .= ' (tag, tag_lc)';
            $query .= ' VALUES (:tag, :tagLower)';
            $bindParams = array(
                ':tag' => $tag->tag,
                ':tagLower' => $tag->tagLower,
            );
        }

        $db->query($query, $bindParams);
        $tagId = (!empty($tag->tagId)) ? $tag->tagId : $db->lastInsertId();
        return $tagId;
    }

    /**
     * link tag to video
     * @param int $tagId id of tag in db
     * @param int $videoId id of video to link tag to
     * @return int id of link row in db
     */
    public function linkTagIdToVideoId($tagId, $videoId)
    {
        $db = Registry::get('db');
        $query = 'INSERT INTO ' . DB_PREFIX . 'tags_videos';
        $query .= ' (tag_id, video_id)';
        $query .= ' VALUES (:tagId, :videoId)';
        $bindParams = array(
            ':tagId' => $tagId,
            ':videoId' => $videoId,
        );
        $db->query($query, $bindParams);
        return $db->lastInsertId();
    }

    /**
     * link list of Tag objects to video
     * @param array<Tag> $tagList array of Tag objects
     * @param int $videoId id of video to link to
     * @return void
     */
    public function linkTagsListToVideoId($tagList, $videoId) 
    {
        foreach ($tagList as $tagObj) {
            $tagDb = $this->getTagByCustom(array('tag_lc' => $tagObj->tagLower));
            if ($tagDb) {
                $tagId = $tagDb->tagId;
            } else {
                // no tag - create
                $tagId = $this->save($tagObj);
            }
            $this->linkTagIdToVideoId($tagId, $videoId);
        }
    }

    public function getTagsFromList(array $tagIds)
    {
        $tagList = array();
        if (empty($tagIds)) return $tagList;

        $db = Registry::get('db');
        $inQuery = implode(',', array_fill(0, count($tagIds), '?'));
        $sql = 'SELECT * FROM ' . DB_PREFIX . 'tags WHERE tag_id IN (' . $inQuery . ')';
        $result = $db->fetchAll($sql, $tagIds);

        foreach($result as $tagRecord) {
            $tagList[] = $this->_map($tagRecord);
        }
        return $tagList;
    }

    public function delete($tagId)
    {
        $db = Registry::get('db');
        $db->query('DELETE FROM ' . DB_PREFIX . 'tags WHERE tag_id = :tagId', array(':tagId' => $tagId));
    }

    /**
     * unlink all linked tags from video
     * @param int $videoId id of video to unlink from
     * @return void
     */
    public function unlinkTagsFromVideoId($videoId)
    {
        $db = Registry::get('db');
        $db->query('DELETE FROM ' . DB_PREFIX . 'tags_videos WHERE video_id = :videoId', array(':videoId' => $videoId));
    }

}