<?php
###################################################################################################
## Jon Wright, IBMS, Academia Sinica, Taipei, 11529, Taiwan
## These files are licensed under the GLP ver 3, essentially you have the right
## to copy, modify and distribute this script but all modifications must be offered
## back to the original authors
###################################################################################################
# This php ver. 7 script askes for a fasta format sequence 
# Then it submits a virhost job to the server

# Retrieve the fasta sequence from the webapge, checks if there is one and then saves it to a file
$fasta = strtoupper($_POST["FASTA"]);
if ($fasta != true) {
    echo "Sorry but you do not appear to have entered a recgonizable fasta sequence, please try again";
    exit();
}

# Call the mkdirFunc and get the target, results directories and random number back
list($rand_target, $target_dir, $result_dir) = mkdirFunc();

# Make the list.txt file and save it to the target directory
$myfile = $target_dir . "input.fasta";
$listfile = fopen($myfile, "w");
fwrite($listfile, ">Submitted input fasta sequence\n");
fwrite($listfile, $fasta);
fclose($listfile);
$errfile = $target_dir . "error.txt"; # write something to the error.txt file
$errfile_handle = fopen($errfile, "w");
fwrite($errfile_handle, "Preparing and checking the input files\n");
fclose($errfile_handle);

# Now submit the job to the qeuue system
if ($ret_var == 0) {
    echo "We will now queue the VirHost job, please wait a few seconds to be directed to the running/results page.<br>";
    echo '/usr/local/bin/qsub -S /bin/bash /var/www/html/virhost/scripts/submit.sub -N V_' . $rand_target . ' -v "random=' . $rand_target . '","ncbiid=' . $ncbiid . '" > ' . $result_dir . 'jobid.txt';
    #exec('/usr/local/bin/qsub -S /bin/bash /var/www/html/virhost/scripts/submit.sub -N V_' . $rand_target . ' -v "random=' . $rand_target . '","ncbiid=' . $ncbiid . '" > ' . $result_dir . 'jobid.txt');
    symlink($target_dir . 'error.txt', $result_dir . 'error_link.txt');
} else {
    exec('rsync -av ' . $target_dir . ' ' . $result_dir);
    exec('echo 999999.limlab >| ' . $result_dir . 'jobid.txt');
}
echo "<meta http-equiv=\"refresh\" content=\"5; URL=http://limlab.dnsalias.org/virhost/results/$rand_target\" />";

# This function makes a unique random number directory in /scratch/working and results
function mkdirFunc() {
    mkdirloop:
        $rand_target = rand(1, 1000000);
        $target_dir = "/scratch/working/" . $rand_target . "/";
        $result_dir = "/var/www/html/virhost/results/" . $rand_target . "/";
        $dir_exists = (is_dir($target_dir));
        if ($dir_exists == false) {
            mkdir($target_dir, 0700);
            mkdir($result_dir, 0700);
            symlink("/var/www/html/virhost/scripts/index.php", "$result_dir/index.php");
        } else {
            gotomkdirloop;
        }
        return array($rand_target, $target_dir, $result_dir);
    }
?>
