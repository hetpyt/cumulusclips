<?php

class TagsCloudMapper extends MapperAbstract
{

    public function getTagsCloudList() {
        $sum = 0;
        $db = Registry::get('db');
        $query = 'SELECT tag, tag_lc, count(tag_id) as tag_count FROM ' . DB_PREFIX . 'tags  GROUP BY tag, tag_lc ORDER BY tag';
        $dbResults = $db->fetchAll($query);

        $tagsCloudList = array();
        foreach($dbResults as $record) {
            $tagItem = $this->_map($record);
            $tagsCloudList[] = $tagItem;
            $sum += $tagItem->tagCount;
        }
        $avg = $sum / count($tagsCloudList);

        foreach($tagsCloudList as $tagItem) {
            if ($tagItem->tagCount > $avg) {
                $tagItem->frequent = true;
            }
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