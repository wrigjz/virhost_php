<?php
###################################################################################################
## Jon Wright, IBMS, Academia Sinica, Taipei, 11529, Taiwan
## These files are licensed under the GLP ver 3, essentially you have the right
## to copy, modify and distribute this script but all modifications must be offered
## back to the original authors
###################################################################################################
# This php ver. 7 script askes for a Virus name and then searches for it in our SQLite DB

# Retrieve the codes - check that we have ones
$input1 = ($_POST["VIRUSNAME"]);

# A test input
#$input1 = "herpes";
if ($input1 != true ) {
    echo "Sorry but you do not appear to have entered a recgonizable Virus name, please try again";
    exit();
}

# Now access the DB and get the data we want back from it, we use PHP mative SQLite commands
# we have to do this twice becasue the test later on takes the 1st result that comes back if
# it is positive
$db = new SQLite3('/var/www/html/virhost/scripts/virus.db');
$outtop = shell_exec('cat /var/www/html/virhost/scripts/results_top.html');
$outbot = shell_exec('cat /var/www/html/virhost/scripts/results_bot.html');
echo $outtop;
$virsearch = preg_replace("/\s+/", "", $input1); # Strip spaces etc out
$virsearch = "%" . $virsearch . "%";
$results = $db->query("select virusname,attachment,receptor,tissue,receptor_host,reference,pubmedid from virhost where virusname like '$virsearch'");

### At this point we should have the data from the DB so we can not test the results and print them
# See if there are any results and if so print out any results we may have found so far
$test = $results->fetchArray();   # Check if we found anything
if ($test == false) {
    echo "Sorry but we have no data for that system.<br>";
} 
else {
    echo '<hr style="border-style: solid; color: black;">';
    echo "<table>";
    #echo "<tr><th>Virus Name</th><th>Attachment</th><th>Receptor</th><th>Tissue</th><th>Receptor Host</th></th><th>References</th><th>PubMed</th></tr>"; 
    echo "<tr><th>Virus Name</th><th>Attachment</th><th>Receptor</th><th>Tissue</th><th>Receptor Host</th></th><th>PubMed</th></tr>"; 
    $results->reset(); # Need to rewind the results back adter the above test
    while ($row = $results->fetchArray()) {
        echo "<tr><td style=\"text-align: center; vertical-align: middle;\">";
        print "{$row['virusname']}";
        echo "</td><td style=\"text-align: center; vertical-align: middle;\">";
        print "{$row['attachment']}";
        echo "</td><td style=\"text-align: center; vertical-align: middle;\">";
        print "{$row['receptor']}";
        echo "</td><td style=\"text-align: center; vertical-align: middle;\">";
        print "{$row['tissue']}";
        echo "</td><td style=\"text-align: center; vertical-align: middle;\">";
        print "{$row['receptor_host']}";
        #echo "</td><td style=\"text-align: center; vertical-align: middle;\">";
        #print "{$row['reference']}";
        echo "</td><td style=\"text-align: center; vertical-align: middle;\">";
        $temp1 = explode(" ", $row['pubmedid']);  # get the pubmed records from the database
        foreach ($temp1 as &$value) { # check each word to see if we print it
            if (substr($value, 0, 4) == "ref.") {  # print ref numbers
                print "$value ";
            }
            if (strlen($value) > 7) { # if its more than 8 characters then its a pubmed record
                $pubmedout = "<a href=\"https://pubmed.ncbi.nlm.nih.gov/$value\">$value</a><br>";
                print $pubmedout;
            }
        }
        echo "</td></tr>";
    }
    echo "</table>"; 
}
# this is how we created the DB
#% sqlite3 virus.db
#sqlite> create table virhost (virusname text, attachment text, receptor text, tissue text, receptor_host text, reference text, pubmedid text);
#sqlite> .separator "^"
#sqlite> .import viruses_table_combined.txt virhost
#sqlite> .separator " "
#sqlite> select virusname from virhost where virusname="Aichi virus";
#sqlite> select virusname from virhost where virusname like '%bat%';

echo $outbot;
?>
