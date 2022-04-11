<?php

class TagMapper extends MapperAbstract
{
    public function getTagById($tagId)
    {
        return $this->getTagByCustom(array('tag_id' => $tagId));
    }

    public function getVideoTagsById($videoId)
    {
        return $this->getMultipleTagsByCustom(array('video_id' => $videoId));
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

    public function getMultipleTagsByCustom(array $params)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'tags  WHERE ';

        $queryParams = array();
        foreach ($params as $fieldName => $value) {
            $query .= "$fieldName = :$fieldName AND ";
            $queryParams[":$fieldName"] = $value;
        }
        $query = preg_replace('/\sAND\s$/', '', $query);
        $dbResults = $db->fetchAll($query, $queryParams);

        $tagsList = array();
        foreach($dbResults as $record) {
            $tagsList[] = $this->_map($record);
        }
        return $tagsList;
    }

    protected function _map($dbResults)
    {
        $tag = new Tag();
        $tag->tagId = (int) $dbResults['tag_id'];
        $tag->videoId = (int) $dbResults['video_id'];
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
            $query .= ' video_id = :videoId, tag = :tag, tag_lc = :tagLower';
            $query .= ' WHERE tag_id = :tagId';
            $bindParams = array(
                ':tagId' => $tag->commentId,
                ':videoId' => $tag->videoId,
                ':tag' => $tag->tag,
                ':tagLower' => $tag->tagLower,
            );
        } else {
            // Create
            $query = 'INSERT INTO ' . DB_PREFIX . 'tags';
            $query .= ' (video_id, tag, tag_lc)';
            $query .= ' VALUES (:videoId, :tag, :tagLower)';
            $bindParams = array(
                ':videoId' => $tag->videoId,
                ':tag' => $tag->tag,
                ':tagLower' => $tag->tagLower,
            );
        }

        $db->query($query, $bindParams);
        $tagId = (!empty($tag->tagId)) ? $tag->tagId : $db->lastInsertId();
        return $tagId;
    }

    /**
     * insert list of Tag objects to db
     * @param array $tagList array of Tag objects
     * @return null
     */
    public function insertTagsList($tagList) {
        if (count($tagList) == 0) {
            return;
        }
        elseif (count($tagList) == 1) {
            $this->save($tagList[0]);
        } else {
            $db = Registry::get('db');
            $query = 'INSERT INTO ' . DB_PREFIX . 'tags';
            $query .= ' (video_id, tag, tag_lc) VALUES ';
            $values = '';
            $bindParams = array();
            foreach ($tagList as $tagObj) {
                $values .= (empty($value) ? '' : ', ') . '(?, ?, ?)';
                $bindParams[] = $tagObj->videoId;
                $bindParams[] = $tagObj->tag;
                $bindParams[] = $tagObj->tagLower;
            }
            $query .= $values;
            $db->query($query, $bindParams);
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

    public function deleteVideoTags($videoId) {
        $db = Registry::get('db');
        $db->query('DELETE FROM ' . DB_PREFIX . 'tags WHERE video_id = :videoId', array(':videoId' => $videoId));
    }

    public function getVideoTagCount($videoId)
    {
        $db = Registry::get('db');
        $sql = 'SELECT COUNT(tag_id) AS count FROM ' . DB_PREFIX . 'tags WHERE video_id = ? AND status = "approved"';
        $result = $db->fetchRow($sql, array($videoId));
        return $result['count'];
    }

}