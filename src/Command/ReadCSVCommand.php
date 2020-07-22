<?php


namespace App\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Helper\Table;

class ReadCSVCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('lib:read-csv')
            ->addArgument('file', InputArgument::REQUIRED, 'Path of the file')
            ->addArgument('json', InputArgument::OPTIONAL, 'Return format JSON')
        ;
    }

     protected function execute(InputInterface $input, OutputInterface $output)
     {

         $file = $input->getArgument('file');

         $products = [];

         $flag = true;
         if (($handle = fopen($file, "r")) !== FALSE) {
             while (($data = fgetcsv($handle,'',';')) !== FALSE) {
                 if($flag) { $flag = false; continue;}
                 $products[] = $data;
             }
         }

        $rows = [];
        for ($i = 0; $i < count($products); $i++){
            $rows[$i][0] = $products[$i][0];
            if($products[$i][2] == 1){
                $rows[$i][1] = "Enable";
            }else{
                $rows[$i][1] = "Disable";
            }
            $rows[$i][2] = str_replace('.', ',', round($products[$i][3],1).' '.$products[$i][4]);
            $rows[$i][3] = preg_replace('#<br\s*?/?>#i', "\n", str_replace('\r', "<br/>", $products[$i][5]));
            $rows[$i][4] = strftime("%A, %#d-%b-%Y %H:%M:%S UTC", strtotime($products[$i][6]));
            $rows[$i][5] = trim(strtolower(preg_replace("/[^\w']+|'(?!\w)|(?<!\w)'/","", str_replace( ' ', '_', $products[$i][1]))));
        }

        if($input->getArgument('json')) {
            echo json_encode($rows, JSON_PRETTY_PRINT);
        }else{
            $section = $output->section();
            $table = new Table($section);
            $table
                ->setHeaders(['Sku', 'Status', 'Price', 'Description', 'Created At', 'Slug'])
                ->setRows($rows);
            $table->render();
        }

     }
}