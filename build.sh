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
php -d phar.readonly=off $PHP_SOURCE/../../vendor/bin/phar-composer build ./build/
#cloudsmith push composer desertrat-io/keepsake keepsake-proto.phar

cd $PROJECT_DIR || exit
