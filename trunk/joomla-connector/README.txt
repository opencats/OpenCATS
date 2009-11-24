This Joomla connector comprises three Joomla components - one module, one component, and one search component (evolved from the image available on the opencats download page).

This connector is known to work with Joomla 1.5.9 and below, and will work by default with OpenCATS v0.9.1a. Some work would be required due to db name changes in order to have it work with OSATS.

The module will display a list of 'sectors' the jobs are available in - for example IT, Management etc
The Component displays the job details, and allows people to apply to jobs.

Options that will need to be changed in order to connect to a different OpenCATS server include;

Description		placeholdername in config files

server IP address	yourcatsservername
MySQL db name		yourcatsdbname
MySQL db username	yourcatsdbusername
MySQL db password	yourcatsdbpassword

(optionally, for a remote OpenCATS installation, you nedd to further define the directory where you want applicant resume's FTP'd to)

This will need to be configured in the three sections;

-catsone module
-catsone component
-catsone search

to ensure full functionality within Joomla.

As a default OpenCATS installation will not categorise jobs by sector, this Joomla module expects to have an 'extra field' defined from within the CATS administration interface, 
please call it "Job Orders"
Within this field you will have the sectors you want to use to categorise your jobs, for example;
	Industrial
	Commercial
	Driving
	Catering
	Information Technology
	Executive

If you want to change these sectors, you need to change the code within the Joomla Module, as it will attempt a match against this extra field.


Remember with a remote OpenCATS server you will need to open the MySQL and FTP ports bwtween your webserver and your OpenCATS server. 
MySQL port is 3306
FTP port is 21

Any, all suport queries to the Opencats forum or the develoment discussion list.

****************
FINAL NOTE: this Joomla module/component is not configured to 'automagically install' as yet.
****************
--

RussH | OpenCats.
