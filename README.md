This is the php scripts and running directory for the VirHost server

#
# It turns the following pipline into a webserver
#Search A: input - genename (example: ACE2)

# to find ncbi 'datasets' and 'dataform' executables check 
# https://www.ncbi.nlm.nih.gov/datasets/docs/command-line-start/

# first I get orthologs for the gene
# in a dedicated directory for a given search, which I assume is
# generated for each search anew, I run a following command:
./datasets download ortholog symbol ACE2 --taxon human        # ace2 is a genename from the input

# as a result ncbi_dataset.zip file is downloaded, so
unzip ncbi_dataset.zip

# this creates 'ncbi_dataset' folder with 'data' subfolder
# then I run alignment using standalone version of Clustal Omega from the top directory
./clustal-omega-1.2.3-macosx -i ./ncbi_dataset/data/protein.faa -o clustal.aln --outfmt=clustal -v --force

# here names protein.faa and clustal.aln are always the same for every search

# then I use 'dataformat' tool (an executable from the same ncbi web page as 'datasets')
# to create a file holding information on gene_id / common animal name
# so in the top direcroty I run

./dataformat tsv gene --inputfile ./ncbi_dataset/data/data_report.jsonl --fields gene-id,tax-name,common-name > names.txt

# Here again, everthing in this command stays the same in every search

# after that, I run python script 'ortholog_analysis.py ACE2' 
# The text output to be shown is stored in
# the generated output file 'output.txt'


 



