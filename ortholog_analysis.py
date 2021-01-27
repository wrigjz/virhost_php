#!/usr/bin/python3
import sys
import os
from Bio.SubsMat import MatrixInfo

#if len(sys.argv) == 1:
#    print("Needs a argument with the list of PDB files to extract the chain from")
#    sys.exit(0)
#
#genesymbol = sys.argv[1] # our search from the input
direc ='./' # the project's directory


def score_match(pair, matrix):
    if pair not in matrix:
        return matrix[(tuple(reversed(pair)))]
    else:
        return matrix[pair]

def score_pairwise(seq1, seq2, matrix, gap_s, gap_e):
    score = 0
    gap = False
    for i in range(len(seq1)):
        pair = (seq1[i], seq2[i])
        if not gap:
            if '-' in pair:
                gap = True
                score += gap_s
            else:
                score += score_match(pair, matrix)
        else:
            if '-' not in pair:
                gap = False
                score += score_match(pair, matrix)
            else:
                score += gap_e
    return score

pam250 = MatrixInfo.pam250

names = []
names_to_animals = {}
gene_id_to_animals = {}

for line in open(direc + 'names.txt'):
    x = line.strip().split('\t')
    if len(x) < 3:
        animal = x[1] +','
    else:
        animal = x[1] + ',' + x[2]
    gene_id_to_animals[x[0].strip()] = animal


for line in open(direc + "ncbi_dataset/data/protein.faa"):
    if ('>' in line) and ('precursor' not in line) and ("Homo sapiens" in line):
        ref = (line.split()[0][1:])
    if ('>' in line):
        name_l = line.split()[0][1:]
        names.append(name_l)
        geneid = line.split('[')[2].split(']')[0][7:]
        names_to_animals [name_l] = gene_id_to_animals[geneid]


align  = {}
count = 0
block = {}

for line in open("clustal.aln"):
    if ("CLUSTAL" not in line) and (line.strip()):
        z =  (line.split())
        if z[0] in names:
          if z[0] != names[-1]:
              block[z[0]] = z[1]
          else:
              block[z[0]] = z[1]
              align[count] = block
              count += 1
              block = {}


def start_ref(ref, align):
   stop = False
   for i in align:
       for j, el in enumerate(align[i][ref]):
           if el != '-':
              beginning = (i,j)
              stop = True
              break
       if stop:
           break
   return beginning    

beginning = start_ref(ref, align)
 
def get_ref_seq(start,count, ref, align):
    seq1 = align[start[0]][ref][start[1]:]
    for i in range(start[0]+1,count):
        seq1 += (align[i][ref])    
    return seq1.strip('-')    

def get_name_seq(start, count, name, align):
    seq2 = align[start[0]][name][start[1]:]
    for i in range(start[0]+1, count):
        seq2 += (align[i][ref])
    return (seq2)

def score_default(seq1, seq2):
    return score_pairwise(seq1, seq2, pam250, 0, 0)/float(len(seq1))

scores = []
for name in names:
    seq1 = get_ref_seq(beginning, count, ref, align)
    seq2 = get_name_seq(beginning, count, name, align)
    score = score_default(seq1, seq2)
    animal = names_to_animals[name]
    scores.append((score, animal))


def sort_scores(scores):
    return (sorted(scores,key= lambda x: x[0], reverse=True))

srt_scores = sort_scores(scores)
with open(direc+'output.txt', 'w') as output:
  for i in range(len(srt_scores)):
    output.write(srt_scores[i][1]+' '+str(srt_scores[i][0])+'\n')



