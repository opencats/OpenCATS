#!/bin/bash
#hi2
MY_PWD=`pwd`
REMOVAL_FILE='scripts/removeWebsiteSymLinks.sh'
RELEASE_REMOVAL_FILE='scripts/makeReleaseRmSymLinks.sh'
ASP_WEBSITE_PATH='modules/asp/website'

# Change path if user isn't in CATS root
while [ ! -d "${ASP_WEBSITE_PATH}" ] && [ `pwd` != "/" ]; do
    echo -n "CATS not found in `pwd`... trying "
    cd ..
    echo -n "`pwd`"
    if [ -d "${ASP_WEBSITE_PATH}" ];
    then
	echo " [OK]"
    else
	echo ", Nope..."
    fi
done
if [ ! -d "${ASP_WEBSITE_PATH}" ];
then
    echo "Cannot find CATS. Please run this script "
    echo "from a path within a CATS installation."
    exit 1
fi

if [ $# -eq 0 ];
then
    echo "This script will automatically create symbolic links for the CATS root "
    echo "folder to the website in the ASP module."
    echo
    echo "If you need a log, please re-run this command as:"
    echo "   makeWebsiteSymLinks.sh > /path/to/logfile"
    echo
    echo -en "Press ENTER to continue or CTRL-C to exit... "
    read
fi

echo "# Automated symbolic link removal for the CATS ASP website module" > $REMOVAL_FILE
echo "# This script should be remove all ASP specific sym-links." >> $REMOVAL_FILE
echo "" >> $REMOVAL_FILE
echo "# Automated symbolic link removal for the CATS ASP website module" > $RELEASE_REMOVAL_FILE
echo "# This script should be remove all ASP specific sym-links." >> $RELEASE_REMOVAL_FILE
echo "" >> $RELEASE_REMOVAL_FILE

function createSymLink {
    # Check if the sym link exists
    if [ -h "${SYM_NAME}" ]
    then
        echo "Symbolic link \"${SYM_NAME}\" exists, skipping."
    else
        echo -en "Creating symbolic link \"${SYM_NAME}\" to \"${ASP_WEBSITE_PATH}/${SYM_NAME}\"... "
        ln -s "${ASP_WEBSITE_PATH}/${SYM_NAME}" "${SYM_NAME}" && echo "Success" || echo "Failed!"
    fi

    # Add a line to a sym links removal script
    echo "echo -en \"Removing the ASP website \\\"${SYM_NAME}\\\" symbolic link... \"" >> $REMOVAL_FILE
    echo "rm -f \"`pwd`/${SYM_NAME}\" 2>/dev/null && echo \"Success\" || echo \"Not Found!\"" >> $REMOVAL_FILE

    # Add a line to a sym links removal script for makeRelease.sh
    echo "echo -en \"Removing the ASP website \\\"${SYM_NAME}\\\" symbolic link... \"" >> $RELEASE_REMOVAL_FILE
    echo "rm -f \"~/catsRC/cats/${SYM_NAME}\" 2>/dev/null && echo \"Success\" || echo \"Not Found!\"" >> $RELEASE_REMOVAL_FILE
}

# Add new Symbolic Links below, just copy and change the name

SYM_NAME='blog'
createSymLink

SYM_NAME='catsnewversion.php'
createSymLink

SYM_NAME='cpl'
createSymLink

SYM_NAME='clientSpecific'
createSymLink

SYM_NAME='cicla'
createSymLink

SYM_NAME='campaigns'
createSymLink

SYM_NAME='bugs'
createSymLink

SYM_NAME='bin'
createSymLink

SYM_NAME='forum'
createSymLink

SYM_NAME='indexingTools'
createSymLink

SYM_NAME='kb_upload'
createSymLink

SYM_NAME='kb'
createSymLink

SYM_NAME='lists'
createSymLink

SYM_NAME='mint'
createSymLink

SYM_NAME='tracker'
createSymLink

SYM_NAME='tarballs'
createSymLink

SYM_NAME='style.css'
createSymLink

SYM_NAME='professional'
createSymLink

chmod a+x "$REMOVAL_FILE"
chmod a+x "$RELEASE_REMOVAL_FILE"

echo
echo "Two script files have been created:"
echo "1) \"${REMOVAL_FILE}\": removes all symbolic links"
echo "2) \"${RELEASE_REMOVAL_FILE}\": removes all symbolic links when executing "
echo "   makeRelease.sh (so sym links aren't tarred into release builds)"
echo

# Return to where we started
cd $MY_PWD
