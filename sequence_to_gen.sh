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

# Now submit the job - send a copy of the command line to error.txt file
echo "Now submitting the job to the qeueue system" >> error.txt
echo "/usr/local/bin/qsub -S /bin/bash /var/www/html/virhost/scripts/submit.sub -N V_$rand_target -v \"random=$rand_target,ncbiid=$ncbiid\" > $result_dir/jobid.txt" >> error.txt
/usr/local/bin/qsub -S /bin/bash /var/www/html/virhost/scripts/submit.sub -N V_$rand_target -v "random=$rand_target,ncbiid=$ncbiid" > $result_dir/jobid.txt

# Remove the previous indecx.html and replace it with index.php
/bin/rm -rf $result_dir/index.html
/bin/ln -s /var/www/html/virhost/scripts/index.php $result_dir/index.php
