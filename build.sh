#!/bin/bash

PROJECT_DIR=$(pwd)

JAVA_SOURCE=$PROJECT_DIR/generated/java/src/main/java
PHP_SOURCE=$PROJECT_DIR/generated/php/build/src
echo "removing old protocols"
rm -rf "${JAVA_SOURCE:?}"/*
rm -rf "${PHP_SOURCE:?}"/*
echo "removal complete"
echo "currently in $PROJECT_DIR"
protoc --proto_path=$PROJECT_DIR --java_out=$JAVA_SOURCE \
--php_out=$PHP_SOURCE $PROJECT_DIR/protocols/**/*.proto

cd $JAVA_SOURCE/../../../ || exit && mvn clean package

cd $PHP_SOURCE/../../ || exit && \

cd $PROJECT_DIR || exit

# todo: fix php generation, use zip files instead to avoid having to go through github actions shenanigans