#!/usr/bin/python3
###################################################################################################
## Karen Sargsyan, IBMS, Academia Sinica, Taipei, 11529, Taiwan
## These files are licensed under the GLP ver 3, essentially you have the right
## to copy, modify and distribute this script but all modifications must be offered
## back to the original authors
###################################################################################################
# This adds the protein to the ncbi dataset
direc = './'

f = open(direc + "ncbi_dataset/data/protein.faa", 'a')
f.write('>INPUT\n')
for line in open('input.fasta'):
    if '>' not in line:
        f.write(line)
f.close()


