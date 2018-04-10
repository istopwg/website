#!/bin/sh
#
# Script to load the SQL database with test data.
#
# The database name and submission directory are pulled from the site.cfg file.
#

if test ! -f site.cfg; then
	echo Please make a copy of site.cfg.tmpl with the local site configuration.
	exit 1
fi

dbname=`grep DB_NAME site.cfg | awk -F\" '{print $2}'`
submitdir=`grep SUBMISSION_DIR site.cfg | awk -F\" '{print $2}'`

echo Database: $dbname
echo Submission Directory: $submitdir

echo Importing pwg.sql...
mysql $dbname <pwg.sql
echo Importing selfcert.sql...
mysql $dbname <selfcert.sql
echo Importing test.sql...
mysql $dbname <test.sql
if test -f local.sql; then
	echo Importing local.sql...
	mysql $dbname <local.sql
fi

echo Creating submission directory...
test -d $submitdir || mkdir -p $submitdir
chmod 777 $submitdir || exit 1

echo Copying files for submission 1...
test -d $submitdir/1 || mkdir $submitdir/1
for file in bonjour document ipp; do
	cp good-$file.plist $submitdir/1/$file.plist
done

echo Copying files for submission 2...
test -d $submitdir/2 || mkdir $submitdir/2
for file in bonjour document ipp; do
	cp bad-$file.plist $submitdir/2/$file.plist
done
