<?php

namespace DEPTRAC_202404\RdKafka;

class KafkaConsumerTopic extends Topic
{
    private function __construct()
    {
    }
    /**
     * @param int $partition
     * @param int $offset
     *
     * @return void
     */
    public function offsetStore($partition, $offset)
    {
    }
}
