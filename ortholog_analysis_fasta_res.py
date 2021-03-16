#!/usr/bin/python3
###################################################################################################
## Karen Sargsyan, IBMS, Academia Sinica, Taipei, 11529, Taiwan
## These files are licensed under the GLP ver 3, essentially you have the right
## to copy, modify and distribute this script but all modifications must be offered
## back to the original authors
###################################################################################################
# this code works when we run FASTA sequence search with important residues mentioned
from Bio.SubsMat import MatrixInfo

direc ='./' # the project's directory

# using substitution matrix score the aligned pair of residues
def score_match(pair, matrix):
    if pair not in matrix:
        return matrix[(tuple(reversed(pair)))]
    else:
        return matrix[pair]

# pairwise score of the two sequences : seq1 and seq2 using substitutution matrix
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

# we are using PAM250 sustitution matrix
pam250 = MatrixInfo.pam250

names = []

# protein sequence ids to verterbate scientific/common name
names_to_animals = {}

# gene ids from the file names.txt to scientific / common names
gene_id_to_animals = {}

for line in open(direc + 'names.txt'):
    x = line.strip().split('\t')
    if len(x) < 3:
        animal = x[1] +','
    else:
        animal = x[1] + ',' + x[2]
    gene_id_to_animals[x[0].strip()] = animal


ref = 'INPUT'

for line in open(direc + "ncbi_dataset/data/protein.faa"):
    if ('>' in line) and 'INPUT' not in line:
        name_l = line.split()[0][1:]
        names.append(name_l)
        geneid = line.split('[')[2].split(']')[0][7:]
        names_to_animals [name_l] = gene_id_to_animals[geneid]

names.append(ref)

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

# starting position of the reference in the alignment
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

transfer = {}


# extract reference sequence 
def get_ref_seq(start,count, ref, align):
    seq1 = align[start[0]][ref][start[1]:]
    for i in range(start[0]+1,count):
        seq1 += (align[i][ref])    
    return seq1.strip('-')    

j = 0

seq1 = get_ref_seq(beginning, count, ref, align)

for i,el in enumerate(seq1):
    if el != '-':
        transfer[j] = i
        j += 1
        

# extracting sequence from the alignment
def get_name_seq(start, count, name, align):
    seq2 = align[start[0]][name][start[1]:]
    for i in range(start[0]+1, count):
        seq2 += (align[i][ref])
    return (seq2)


def score_default(seq1, seq2):
    return score_pairwise(seq1, seq2, pam250, 0, 0)/float(len(seq1))


# analysis of the scores and printing output
# read residues that are already checked against fasta file

with open('imp_residues.txt', 'r') as f:
    imp_residues = f.readline().strip().split(',')


def res_analysis(seq, imp_residues, transfer):
    result = []
    for el in imp_residues:
        pos = int(el[1:])-1
        aa  = el[0]
        pos_new = transfer[pos]
        if seq[pos_new] == aa:
            result.append('*')
        elif seq[pos_new] == '-':
            result.append('-')
        elif seq[pos_new] != aa and score_match((seq[pos_new], aa), pam250) > 0.5:
            result.append(seq[pos_new])
        else:
            result.append('-')
    return result        


def score_results(result):
    conserved = 0.0
    substituted = 0.0
    total = len(result)
    for el in result:
        if el == '*':
            conserved += 1
        elif el != '-':
            substituted += 1
    return (conserved*100.0/total,substituted*100.0/total)        

scores = []
for name in names[:-1]:
    seq2 = get_name_seq(beginning, count, name, align)
    score = score_default(seq1, seq2)
    animal = names_to_animals[name]
    result = res_analysis(seq2, imp_residues, transfer)
    result_score = score_results(result)
    scores.append((result_score, result, animal))  # check this out and some other steps...

def sort_scores(scores):
    return (sorted(scores,key= lambda x: x[0][0], reverse=True))

def show_result(result):
    return ','.join(result).strip()

srt_scores = sort_scores(scores)

# provide a proper output of the results
with open(direc+'output_redundant.txt', 'w') as output:
  output.write('Scientific name, Common name,'+ ','.join(imp_residues)+'\n')
  for i in range(len(srt_scores)):
    output.write(srt_scores[i][2]+','+(show_result(srt_scores[i][1]))+'\n')

animals = []

with open('output.txt','w') as f:
  for line in open('output_redundant.txt'):
      z = line.split(',')
      if z[0] not in animals:
          animals.append(z[0])
          f.write(line)
