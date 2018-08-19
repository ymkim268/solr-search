import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;
import org.jsoup.select.Elements;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileReader;
import java.io.PrintWriter;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Map;
import java.util.Set;

public class ExtractLinks {
    
    public static void printFileMap(Map<String, String> m) {
        System.out.println("=== Printing Map ===");
        for(Map.Entry<String, String> e : m.entrySet()) {
            System.out.println(e.getKey() + " => " + e.getValue());
        }
        System.out.println("\tsize of map = " + m.size());
    }
    
    public static void printUrlMap(Map<String, Set<String>> m) {
        System.out.println("=== Printing Map ===");
        for(Map.Entry<String, Set<String>> e : m.entrySet()) {
            System.out.println(e.getKey() + " => " + e.getValue());
        }
        System.out.println("\tsize of map = " + m.size());
    }
    
    public static void main(String[] args) throws Exception {
        
        String mappingFile = "/Users/ymkim/Desktop/cs572-hw4-workspace/FOX_News/UrlToHtml_foxnews.csv";
        String fileDir = "/Users/ymkim/Desktop/cs572-hw4-workspace/FOX_News/HTML_files";
        String edgelistPath = "/Users/ymkim/Desktop/cs572-hw4-workspace/edgelist.txt";
        
        File mapping = new File(mappingFile);
        Map<String, String> fileUrlMap = new HashMap<String, String>(); // file name -> url
        Map<String, String> urlFileMap = new HashMap<String, String>(); // url -> file name
        
        File edgelist = new File(edgelistPath);
        PrintWriter pw = new PrintWriter(edgelist);
        
        int size = 0;
        int duplicates = 0;
        
        // setup fileUrlMap and urlFileMap
        try {
            BufferedReader br = new BufferedReader(new FileReader(mapping));
            String line;
            while((line = br.readLine()) != null) {
                String[] row = line.split(",");
                fileUrlMap.put(row[0], row[1]);
                
                if(urlFileMap.containsKey(row[1])) {
                    duplicates++;
                }
                urlFileMap.put(row[1], row[0]);
            }
        } catch (Exception e) {
            e.printStackTrace();;
        }
        
        System.out.println("Duplicates = " + duplicates);
        
        File dir = new File(fileDir);
        Set<String> edges = new HashSet<String>();        
        
        for(File file : dir.listFiles()) {
            String fileName = file.getName();
            
            if(fileName.contains("html")) {
                size++;
                
                Document doc = Jsoup.parse(file, "UTF-8", fileUrlMap.get(file.getName()));
                Elements links = doc.select("a[href]");
                
                for(Element link: links) {
                    String url = link.attr("href").trim();
                    if(urlFileMap.containsKey(url)) {
                        edges.add(file.getName() + " " + urlFileMap.get(url));
                    }
                }
                
            }
            
        }
        System.out.println("-> proccessed = " + size);
        
        for(String e: edges) {
            pw.println(e);
        }
        System.out.println("-> num of edges = " + edges.size());
        
        pw.flush();
        pw.close();

        
    }
}