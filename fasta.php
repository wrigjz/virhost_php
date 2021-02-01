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

# write something to the error.txt file so we know we have at least reached here
$errfile = $target_dir . "error.txt";
$errfile_handle = fopen($errfile, "w");
fwrite($errfile_handle, "Preparing and checking the input files\n");
fclose($errfile_handle);

# Now set up the scripts to find the gene symbol code and put it into background
# Write out a dummy jobid.txt so that the index.php file has something to check on for its reports
if ($ret_var == 0) {
    echo "We are currently trying to get a gene symbol code for your fasta sequence<br>\n";
    echo "Please wait a minute while we do that, once that is done we will submit your job to the qeueue<br>\n";
    $shell1 = escapeshellarg($rand_target); # To pass an argument to a basj shell we
    $shell2 = escapeshellarg($result_dir);  # have to use this format
    $shell3 = escapeshellarg($target_dir);  # Otherwise they come up empty
    echo "/var/www/html/virhost/scripts/sequence_to_gen.sh $shell1 $shell2 $shell3 &";
    shell_exec("/bin/bash /var/www/html/virhost/scripts/sequence_to_gen.sh $shell1 $shell2 $shell3 > /dev/null 2>&1 &");
    symlink($target_dir . 'error.txt', $result_dir . 'error_link.txt');
    exec('echo Getting gene >| ' . $result_dir . 'jobid.txt');
} else {
    exec('rsync -av ' . $target_dir . ' ' . $result_dir);
    exec('echo 999999.limlab >| ' . $result_dir . 'jobid.txt');
}
# Give it 5seconds and then move to the results directory and run the html from there
echo "<meta http-equiv=\"refresh\" content=\"5; URL=https://virhost.limlab.dnsalias.org/results/$rand_target\" />";

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
