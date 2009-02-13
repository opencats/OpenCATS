#!/bin/bash

# Information about the server where the deleted attachments will be stored
STORE_SERVER="fs1.cognizo.com"
STORE_PATH="/var/www/html/andrew-test/temp"
STORE_USERNAME="cats"
STORE_PASSWORD="password"

DELETED_ATTACHMENTS_PATH="/usr/local/www/catsone.com/data/attachments/deleted"
DELETED_ATTACHMENTS_PATH="/home/andrew/test/deleted"
SCP_PATH="/usr/bin/scp"
 
# *********************************************************************************
# DO NOT MAKE CHANGES BELOW THIS LINE
# *********************************************************************************

# Use values from ~/ENVIRONMENT.conf (if present)
if [ -f ~/ENVIRONMENT.conf ];
then
    DELETED_ATTACHMENTS_PATH=`cat ~/ENVIRONMENT.conf | grep "DELETED_ATTACHMENTS_PATH=\"" | sed -e "s/DELETED_ATTACHMENTS_PATH=\"//" | sed -e "s/\"//"`
    SCP_PATH=`cat ~/ENVIRONMENT.conf | grep "SCP_PATH=\"" | sed -e "s/SCP_PATH=\"//" | sed -e "s/\"//"`
fi

if [ ! -d $DELETED_ATTACHMENTS_PATH ];
then
    echo "Path to deleted attachments \"${DELETED_ATTACHMENTS_PATH}\" not not exist, aborting."
    exit 1
fi

attachment_files=`ls -l ${DELETED_ATTACHMENTS_PATH} | awk '{print $8}'`
for file_name in $attachment_files
do
    file_path="${DELETED_ATTACHMENTS_PATH}/${file_name}"
    if [ -f $file_path ];
    then
        $SCP_PATH "$file_path" "${STORE_SERVER}:${STORE_PATH}/${file_name}" 2>/dev/null || \
	    ( echo "Failed to transfer \"${file_name}\" to ${STORE_SERVER}:/${STORE_PATH}!"; ) && \
	    ( echo "Transfered \"${file_name}\" to ${STORE_SERVER}:/${STORE_PATH}."; rm -f "${file_path}"; )
    fi
done

exit 0
