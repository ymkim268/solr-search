from __future__ import print_function

import networkx as nx

def main():
	out_path = "/Users/ymkim/Desktop/cs572-hw4-workspace/external_pageRankFile.txt"
	root_path = "/Users/ymkim/Desktop/cs572-hw4-workspace/FOX_News/HTML_files"

	G = nx.Graph()
	G = nx.read_edgelist("/Users/ymkim/Desktop/cs572-hw4-workspace/edgelist.txt", create_using=nx.DiGraph())
	pr = nx.pagerank(G, alpha=0.85,personalization=None, max_iter=30, tol=1e-06, nstart=None, weight='weight', dangling=None)

	print("num of nodes = {}".format(G.number_of_nodes()))
	print("num of edges = {}".format(G.number_of_edges()))

	f = open(out_path, 'w')
	for i in pr.items():
		# print("{}/{}={}".format(root_path, i[0], i[1]))
		f.write("{}/{}={}\n".format(root_path, i[0], i[1]))

	f.close()
	print("done writing to pagerank.txt")

if __name__ == "__main__":
	main()