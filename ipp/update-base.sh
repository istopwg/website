#!/bin/sh
cp base-registrations.xml base-registrations.TEMP
for file in pwg51[0-9][0-9].[0-9]*.txt; do
	title=`basename $file .txt | awk '{print toupper($1);}'`
	url=`head -1 $file`
	./register -x $url -t $title base-registrations.xml $file
done
mv base-registrations.TEMP base-registrations.xml.O
