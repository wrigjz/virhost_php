<?php
###################################################################################################
## Jon Wright, IBMS, Academia Sinica, Taipei, 11529, Taiwan
## These files are licensed under the GLP ver 3, essentially you have the right
## to copy, modify and distribute this script but all modifications must be offered
## back to the original authors
###################################################################################################
# The is the master index file for the VirHost Webserver - it is based on php ver. 7,
# If a job is queued or running it tells you and gives the position and refreshes each minute

# The main driver code is here, we get the jobid and then check the queue system to see if it is
# Queued or Running or exiting, if it is neither then we check for a results file and if there is one we assume it is finished
# If it is not in the queue system and the results file is empty we assume it has failed
#
# Open the random.txt file to get the job number from the file saved when it was submitted
$jobfile = fopen("jobid.txt", "r") or die("Unable to open file!");
$jobid = fgets($jobfile);
fclose($jobfile);

# Check if the jobfile has actually been created or if it is a placeholder while getting the gene symbol
if ($jobid == "Getting gene\n") {
    genesymbol();
    echo "<meta http-equiv=\"refresh\" content=\"60\"/>";
} else {
    # Get the status and give the status as the header
    $my_temp = shell_exec("/usr/local/bin/qstat | grep $jobid");
    $job_status = preg_split('/\s+/', $my_temp);
    $found = isset($job_status[4]); # if the job is in the queue this will be set
    if ($found == true) {
        if ($job_status[4] == "Q") {
            queuedup($jobid);
        } elseif ($job_status[4] == "R" || $job_status[4] == "E") {
            running($jobid);
        } else { # Job was not in the queue system, ideally we should never get to this line
            failed($jobid);
        }
    } elseif (filesize("output.txt") != 0) { # Check for an output.txt file, if it exists and is not zero then we have finished
        finished($jobid);
    } else { # IF we are here then the job is not in the queue and the output.txt file is empty so we failed!
        failed($jobid);
    }
}

# The functions sections
# The status function is called from both the queued and running jobs to give feedback to the user
# about what point their job has reached., this works by grepping the error_link.txt file for lines
# that are printed out as the job progresses
function status() {
    echo "Prepared inputs: ";
    exec('grep "Getting ready to submit to queue system" error_link.txt', $out, $ret_val);
    if ($ret_val == 0) {
        echo "&#9745<br>";
    } else {
        echo "&#9744<br>";
    }
    echo "Started retriveing the dataset: ";
    exec('grep "codes from ncbi" error_link.txt', $out, $ret_val);
    if ($ret_val == 0) {
        echo "&#9745<br>";
    } else {
        echo "&#9744<br>";
    }
    echo "Unzipping the dataset: ";
    exec('grep "Unzipping the dataset" error_link.txt', $out, $ret_val);
    if ($ret_val == 0) {
        echo "&#9745<br>";
    } else {
        echo "&#9744<br>";
    }
    echo "Aligning the sequences: ";
    exec('grep "Running Clustal Omega" error_link.txt', $out, $ret_val);
    if ($ret_val == 0) {
        echo "&#9745<br>";
    } else {
        echo "&#9744<br>";
    }
    echo "Running dataformat: ";
    exec('grep "Running dataformat" error_link.txt', $out, $ret_val);
    if ($ret_val == 0) {
        echo "&#9745<br>";
    } else {
        echo "&#9744<br>";
    }
    echo "Running the analysis: ";
    exec('grep "Running ortholog_analysis" error_link.txt', $out, $ret_val);
    if ($ret_val == 0) {
        echo "&#9745<br>";
    } else {
        echo "&#9744<br>";
    }
    echo "<meta http-equiv=\"refresh\" content=\"60\"/>";
}

# The function for if a job is missing or failed
function failed($jobid) {
    echo "<head>";
    echo "<title>::: Failed at the VirHost server :::</title>";
    echo "<meta charset=\"utf-8\">";
    echo "</head>";
    echo "<body BGCOLOR=\"#FFFFFF\">";
    echo "<center> <img src=\"../../images/as-en_07.gif\" alt=\"Academia Sinica Logo\">";
    echo "<H2>VirHost sadly failed.</H2>";
    echo "</center>";
    echo "<H2>Your job $jobid has failed for some reason.</H2>";
    if (filesize("error.txt") != 0) {
        exec('grep "Unable to get a gene symbol from the submitted fasta sequence" error.txt', $out, $ret_val);
        if ($ret_val == 0) {
            echo "It looks like we were unable to get a gene symbol for your submitted fasta sequence, are you sure it is correct?<br>";
        }
        exec('grep "are you sure it is a correct gene symbol" error.txt', $out, $ret_val);
        if ($ret_val == 0) {
            echo "It looks like we were unable to find your gene symbol in the ncbi, are you sure it is correct? <br>";
        }
        echo "To try to get a better idea what went wrong ";
        echo "you can try looking at the <a href=\"error.txt\">error.txt</a> file, the last lines should indicated the issue.<br>";
    }
}

# The function for if we find a job is queued
function queuedup($jobid) {
    echo "<head>";
    echo "<title>::: Queued  at the VirHost residue server :::</title>";
    echo "<meta charset=\"utf-8\">";
    echo "</head>";
    echo "<body BGCOLOR=\"#FFFFFF\">";
    echo "<center> <img src=\"../../images/as-en_07.gif\" alt=\"Academia Sinica Logo\">";
    echo "<H2>Queued and waiting to run VirHost.</H2>";
    echo "</center>";
    echo "<H2>Your job is $jobid and is currently in the queue for calculation.</H2>";
    echo "This page will be updated every minute";
    # Find queue status
    echo "<pre>Q order  Q number                  Q Name<br></pre>";
    $my_status = shell_exec("/usr/local/bin/qstat | nl -v -2 | grep apache");
    echo "<pre>$my_status</pre>";
    status(); # Call the status function
}

# The function for if we find a job is running
function running($jobid) {
    echo "<head>";
    echo "<title>::: Running  at the VirHost residue server :::</title>";
    echo "<meta charset=\"utf-8\">";
    echo "</head>";
    echo "<body BGCOLOR=\"#FFFFFF\">";
    echo "<center> <img src=\"../../images/as-en_07.gif\" alt=\"Academia Sinica Logo\">";
    echo "<H2>Running at VirHost.</H2>";
    echo "</center>";
    echo "<H2>Your job is $jobid and is currently running.</H2>";
    echo "This page will be updated every minute";
    # Find my job and print it out
    echo "<pre>Q order  Q number                  Q Name<br></pre>";
    $my_status = shell_exec("/usr/local/bin/qstat | nl -v -2 | grep apache");
    echo "<pre>$my_status</pre>";
    status(); # Call the status function
}

# The function for if we find a job is finished
function finished($jobid) {
    # array to hold the uppercase to lowercase resname
    echo "<head>";
    echo "<title>::: Finished  at the VirHost residue server :::</title>";
    echo "<meta charset=\"utf-8\">";
    echo "</head>";
    echo "<body BGCOLOR=\"#FFFFFF\">";
    echo "<center> <img src=\"../../images/as-en_07.gif\" alt=\"Academia Sinica Logo\">";
    echo "<h1>Results from <a href=\"https://virhost.limlab.dnsalias.org\">VirHost</a> - The Potential Hosts for Human Viruses Server";
    echo "</center>";
    # Now reate the webpage itself
    echo "Your job has finished and the results are available below.<br>";
    echo "<p>The results can also be downloaded from <a href=\"output.txt\">here</a>.";
    echo "<p>The intermediate file  can also be downloaded from <a href=\"output_redundant.txt\">here</a>.";
    echo "<p>The zip file of orthologs can be downloaded from <a href=\"ncbi_dataset.zip\">here</a>.";
    echo "<p>The clustal omega alignment can be downloaded from <a href=\"clustal.aln\">here</a>.";
    if (file_exists("imp_residues.txt")) {  # Extra comments for the important residues
        echo "<p>The output is a comma separated file with important residues given in order they've been provided in the query.";
        echo "<br>* means that the corresponding amino acid remains the same as it is given in the query";
        echo "<br>- means an amino acid has been changed and substitution is non conservative";
        echo "<br>For the rest of cases of conservative substitution, a single letter amino acid name is provided to show the change";
    }
    echo "<pre>";
    echo file_get_contents( "output.txt" ); // get the contents, and echo it out.
    echo "</pre>";
    echo "<hr style=\"border-style: solid; color: black;\">";
    echo "<a href=\"https://virhost.limlab.dnsalias.org\">VirHost</a> is hosted at <a href=\"http://www.ibms.sinica.edu.tw\">The Institute of Biomedical Sciences</a>, <a href=\"http://www.sinica.edu.tw\">Academia Sinica</a>, Taipei 11529, Taiwan.";
    echo "<hr style=\"border-style: solid; color: black;\">";
}

function genesymbol() {
     echo "<head>";
     echo "<title>::: Preparing  at the VirHost residue server :::</title>";
     echo "<meta charset=\"utf-8\">";
     echo "</head>";
     echo "<body BGCOLOR=\"#FFFFFF\">";
     echo "<center> <img src=\"../../images/as-en_07.gif\" alt=\"Academia Sinica Logo\">";
     echo "<H2>VirHost.<H2>";
     echo "</center>";
     echo "This page will be updated every minute<br>";
     echo "At the present time the server is attempting to retrieve a gene code from your fasta sequence.<br>";
     echo "When that step is completed then gene code will be submitted to the queue for processing.<br>";
     echo "When that happens this page will automatically refresh to give status updates.<br>";
     echo "Preparing inputs: &#9744";
     echo "<hr style=\"border-style: solid; color: black;\">";
     echo "<a href=\"https://virhost.limlab.dnsalias.org\">VirHost</a> is hosted at <a href=\"http://www.ibms.sinica.edu.tw\">The Institute of Biomedical Sciences</a>, <a href=\"http://www.sinica.edu.tw\">Academia Sinica</a>, Taipei 11529, Taiwan.";
     echo "<hr style=\"border-style: solid; color: black;\">";
}
?>
