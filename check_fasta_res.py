#!/usr/bin/python3
###################################################################################################
## Karen Sargsyan, IBMS, Academia Sinica, Taipei, 11529, Taiwan
## These files are licensed under the GLP ver 3, essentially you have the right
## to copy, modify and distribute this script but all modifications must be offered
## back to the original authors
###################################################################################################
with open('imp_residues.txt', 'r') as f:
    imp_residues = f.readline().strip().split(',')

seq = ''
for line in open('input.fasta'):
    if '>' not in line:
        seq += line.strip()

for el in imp_residues:
    if seq[int(el[1:])-1] != el[0]:
        print ('wrong')
        break


