<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\People;
use App\Entity\CsvImport;

use App\Service\PeopleService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Doctrine\ORM\EntityManagerInterface;



class Test2Controller extends AbstractController
{
    private $peopleService;
    private $em;

    public function __construct(PeopleService $peopleService, EntityManagerInterface $em)
    {
        $this->peopleService = $peopleService;
        $this->em = $em;
    }

    /**
     * @Route("/test2", methods={"GET"})
     */
    public function index()
    {   
        ini_set('MAX_EXECUTION_TIME', -1);
        set_time_limit(7200);
        ini_set('memory_limit', '-1');
        $response = new StreamedResponse(function () {
            $fp = fopen('php://output', 'w');
            fputcsv($fp, ['id', 'firstname', 'lastname', 'initials', 'dob', 'age']);
            $people = $this->peopleService->createList(1000000);
             foreach ($people as $fields) {
                fputcsv($fp, $fields);
             }
            fclose($fp);
        });
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment;filename=output.csv');
        return $response;
    }

    /**
     * @Route("/test2/import")
     */
    public function import(Request $request)
    {
        ini_set("upload_max_filesize","300M");
        ini_set('MAX_EXECUTION_TIME', -1);
        set_time_limit(7200);
        ini_set('memory_limit', '-1');
        $form = $this->createFormBuilder()
        ->add('submitFile', FileType::class, [
            'data_class' => null,
            'multiple' => true,
            'label' => 'File  to Submit'
        ])
        ->getForm();

        if ($request->getMethod('post') == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $file = $form->get('submitFile');

                $filedatat = $file->getData();

                if (($file) !== FALSE) {
                    $handle = fopen($filedatat[0]->getRealPath(),'rb');
                    $filepath = $filedatat[0]->getRealPath();
                    $i = 0;
                    $batchSize = 10500;
                    $conn= $this->em->getConnection();
                    $sql = "INSERT INTO csv_import (firstname, lastname, initial, age, dob) VALUES ";

                    while (($row = fgetcsv($handle)) !== FALSE) {
                        if($i > 0){
                         $dob = \DateTime::createFromFormat('m-d-Y', $row[4]);
                         $sql .= " ('".$row[1]."', '".$row[2]."', '".$row[3]."', ".$row[5].", '".$dob->format('Y-m-d')."' ) "; 

                         if (($i % $batchSize) === 0) {
                            $stmt = $this->em->getConnection()->prepare($sql);
                            $stmt->execute();
                            $sql = "INSERT INTO csv_import (firstname, lastname, initial, age, dob) VALUES ";
                         }else{
                             if($i != 1000000){
                                $sql .= ",";
                            }
                         }
                         
                        }
                        $i++;
                    }
                    $stmt = $this->em->getConnection()->prepare($sql);
                    $stmt->execute();
                }
            }
        }

        return $this->render('test2.html.twig', ['form' => $form->createView()] );
    }
}
