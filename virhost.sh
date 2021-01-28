#!/bin/bash


# setup the envirnment
echo "Setting up the Anaconda environment" >> error.txt
echo "We are running on $1" >> error.txt
date >> error.txt
source /home/programs/anaconda/linux-5.3.6/init.sh
error=$?
if [ $error -ne 0 ] ; then
    echo "Unable to source the anaconda environment" >> error.txt
    echo $error >> error.txt
    exit 1
fi
wait

# retreieve the codes from ncbi
echo "Retrieveing the $1 codes from ncbi" >> error.txt
/home/programs/ncbi-blast/datasets download ortholog symbol $1 --taxon human > /dev/null 2>>error.txt
if [ $error -ne 0 ] ; then
    echo "Unable to get the $1 code from ncbi" >> error.txt
    echo $error >> error.txt
    exit 1
fi
wait

sleep 20
# unzip the dataset
echo "Unzipping the dataset" >> error.txt
unzip ncbi_dataset.zip > /dev/null 2>>error.txt
if [ $error -ne 0 ] ; then
    echo "Unable to unzip the downloaded set" >> error.txt
    echo $error >> error.txt
    exit 1
fi
wait

# Alidn using clustal omega
echo "Running Clustal Omega" >> error.txt
/home/programs/clustalw2.1_linux/bin/clustalo-1.2.4-Ubuntu-x86_64 -i ./ncbi_dataset/data/protein.faa \
    -o clustal.aln --outfmt=clustal -v --force >> error.txt 2>&1
if [ $error -ne 0 ] ; then
    echo "Clustal Omega failed" >> error.txt
    echo $error >> error.txt
    exit 1
fi
wait

# Sort the results
echo "Running dataformat" >> error.txt
/home/programs/ncbi-blast/dataformat tsv gene --inputfile ./ncbi_dataset/data/data_report.jsonl \
    --fields gene-id,tax-name,common-name > names.txt 2>>error.txt
if [ $error -ne 0 ] ; then
    echo "Dataformat failed" >> error.txt
    echo $error >> error.txt
    exit 1
fi
wait

echo "Running ortholog_analysis" >> error.txt
python3 /var/www/html/virhost/scripts/ortholog_analysis.py >> error.txt 2>&1
if [ $error -ne 0 ] ; then
    echo "Dataformat failed" >> error.txt
    echo $error >> error.txt
    exit 1
fi
wait

echo "All done" >> error.txt
