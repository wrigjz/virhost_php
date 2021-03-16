#!/bin/bash
###################################################################################################
## Jon Wright, IBMS, Academia Sinica, Taipei, 11529, Taiwan
## These files are licensed under the GLP ver 3, essentially you have the right
## to copy, modify and distribute this script but all modifications must be offered
## back to the original authors
###################################################################################################
# This script takes the input from the php fasta script and starts the process of getting the gene symbol
rand_target=$1
result_dir=$2
target_dir=$3

# Move to the working directory and put in things to the error file for debugging

cd $target_dir
echo $rand_target >| error.txt
echo $result_dir >> error.txt
echo $target_dir >> error.txt

# setup environment
source /home/programs/anaconda/linux-5.3.6/init.sh

# Get the code from ncbi
echo "Getting the code from NCBI" >> error.txt
ncbiid=`python3 /var/www/html/virhost/scripts/sequence_to_gen.py`
error=$?
if [ "$ncbiid" == "" ] || [ $error -ne 0 ] ; then
    echo "Unable to get a gene symbol from the submitted fasta sequence" >> error.txt
    echo $error >> error.txt
    /bin/rm -rf $result_dir/index.html
    /bin/ln -s /var/www/html/virhost/scripts/index.php $result_dir/index.php
    /bin/cp error.txt $result_dir/error.txt
    echo "alas," >|  $result_dir/jobid.txt
    exit 1
fi
wait

# Now submit the job - send a copy of the command line to error.txt file
echo "Getting ready to submit to queue system" >> error.txt
if [ -s imp_residues.txt ] ; then # important exists and is not empty then pipline B2
    echo "/usr/local/bin/qsub -S /bin/bash /var/www/html/virhost/scripts/submitB2.sub -N V_$rand_target -v \"random=$rand_target,ncbiid=$ncbiid\" >| $result_dir/jobid.txt" >> error.txt
    /usr/local/bin/qsub -S /bin/bash /var/www/html/virhost/scripts/submitB2.sub -N V_$rand_target -v "random=$rand_target,ncbiid=$ncbiid" >| $result_dir/jobid.txt
else # important does not exist - or does but is empty then pipline B1
    echo "/usr/local/bin/qsub -S /bin/bash /var/www/html/virhost/scripts/submitB1.sub -N V_$rand_target -v \"random=$rand_target,ncbiid=$ncbiid\" >| $result_dir/jobid.txt" >> error.txt
    /usr/local/bin/qsub -S /bin/bash /var/www/html/virhost/scripts/submitB1.sub -N V_$rand_target -v "random=$rand_target,ncbiid=$ncbiid" >| $result_dir/jobid.txt
fi

# Remove the previous indecx.html and replace it with index.php
/bin/rm -rf $result_dir/index.html
/bin/ln -s /var/www/html/virhost/scripts/index.php $result_dir/index.php
