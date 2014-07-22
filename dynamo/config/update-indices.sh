#!/bin/sh
if test $# = 1; then
	verbose=$1
else
	verbose=0
fi

cd /var/www/www.msweet.org
for config in config/*.config; do
	/usr/local/bin/swish-e -v $verbose -c $config >/dev/null 2>&1
done
