<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# NO CHECKED-IN PROTOBUF GENCODE
# source: protocols/common/common-response-meta.proto

namespace GPBMetadata\Protocols\Common;

class CommonResponseMeta
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
          return;
        }
        \GPBMetadata\Google\Protobuf\Timestamp::initOnce();
        $pool->internalAddGeneratedFile(
            "\x0A\x8C\x03\x0A+protocols/common/common-response-meta.proto\x12\x0Fkeepsake.common\"\xC3\x02\x0A\x12CommonResponseMeta\x12\x14\x0A\x07message\x18\x01 \x01(\x09H\x00\x88\x01\x01\x122\x0A\x09timestamp\x18\x03 \x01(\x0B2\x1A.google.protobuf.TimestampH\x01\x88\x01\x01\x12\x17\x0A\x0Aservice_id\x18\x04 \x01(\x0DH\x02\x88\x01\x01\x12A\x0A\x07headers\x18\x05 \x03(\x0B20.keepsake.common.CommonResponseMeta.HeadersEntry\x12\x1B\x0A\x0Ecorrelation_id\x18\x06 \x01(\x09H\x03\x88\x01\x01\x1A.\x0A\x0CHeadersEntry\x12\x0B\x0A\x03key\x18\x01 \x01(\x09\x12\x0D\x0A\x05value\x18\x02 \x01(\x09:\x028\x01B\x0A\x0A\x08_messageB\x0C\x0A\x0A_timestampB\x0D\x0A\x0B_service_idB\x11\x0A\x0F_correlation_idb\x06proto3"
        , true);

        static::$is_initialized = true;
    }
}

