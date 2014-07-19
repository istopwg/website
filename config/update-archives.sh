#!/bin/sh
cd /var/lib/mailman/archives/private
for list in abnf epm htmldoc imatting keyboardfun mxml newsd rasterview; do
	rm -rf $list
	~mailman/bin/arch $list
done
