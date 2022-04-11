<?php

class TagsCloudMapper extends MapperAbstract
{

    public function getTagsCloudList() {
        $db = Registry::get('db');
        $query = 'SELECT tag, tag_lc, count(tag_id) as tag_count FROM ' . DB_PREFIX . 'tags  GROUP BY tag, tag_lc ORDER BY tag';
        $dbResults = $db->fetchAll($query);

        $tagsCloudList = array();
        foreach($dbResults as $record) {
            $tagsCloudList[] = $this->_map($record);
        }
        return $tagsCloudList;
    }


    protected function _map($dbResults)
    {
        $tagCloud = new TagsCloud();
        $tagCloud->tag = $dbResults['tag'];
        $tagCloud->tagLower = $dbResults['tag_lc'];
        $tagCloud->tagCount = (int) $dbResults['tag_count'];
        return $tagCloud;
    }

}