#!/usr/bin/python3
###################################################################################################
## Karen Sargsyan, IBMS, Academia Sinica, Taipei, 11529, Taiwan
## These files are licensed under the GLP ver 3, essentially you have the right
## to copy, modify and distribute this script but all modifications must be offered
## back to the original authors
###################################################################################################
from Bio.Blast import NCBIWWW
from Bio.Blast import NCBIXML
E_VALUE_THRESH = 1e-20
sequence_data = open("input.fasta").read() # let's say an input fasta is saved as temprorary file
result_handle = NCBIWWW.qblast("blastp", "swissprot", sequence_data, entrez_query="txid9606[ORGN]")
results = []

# results.xml is a temprorary file storing results of the blast search
with open('results.xml', 'w') as save_file:
   blast_results = result_handle.read()
   save_file.write(blast_results)

# getting gene name
for record in NCBIXML.parse(open("results.xml")):
      if record.alignments:
         #print("\n")
         #print("query: %s" % record.query[:100])
         for align in record.alignments:
             for hsp in align.hsps:
                 if hsp.expect < E_VALUE_THRESH:
                      results.append("match: %s " % align.title[:100])


if len(results)>0:
   check = results[0].split('|')[1].split('.')[0]
   for line in open('/scratch/HUMAN_9606_idmapping_selected.tab'):
       if check == line.split()[0].strip():
           genename =  (line.split()[1].split('_')[0])
           print (genename)
           break
   
