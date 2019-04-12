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
            $people = $this->peopleService->createList(10);
             foreach ($people as $fields) {
                fputcsv($fp, $fields);
             }
            //$this->peopleService->createList(30, $fp);
            fclose($fp);
        });
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment;filename=pressurecsv.csv');
        return $response;
    }

    /**
     * @Route("/test2/import")
     */
    public function import(Request $request)
    {
        $form = $this->createFormBuilder()
        ->add('submitFile', FileType::class, [
            'data_class' => null,
            'multiple' => true,
            'label' => 'File  to Submit'
        ])
        ->getForm();

        // Check if we are posting stuff
        if ($request->getMethod('post') == 'POST') {
            // Bind request to the form
            $form->handleRequest($request);
            var_dump($form->get('submitFile'));
            // If form is valid
            if ($form->isValid()) {
                // Get file
                $file = $form->get('submitFile');
                // Your csv file here when you hit submit button
                //$file->getData();
                $filedatat = $file->getData();
               // var_dump(get_class_methods($filedatat[0])); die();
                var_dump(get_class_methods($file));

                if (($file) !== FALSE) {
                    $handle = fopen($filedatat[0]->getRealPath(),'rb');
                    $i = 0;
                    $batchSize = 500;
                    while (($row = fgetcsv($handle)) !== FALSE) {
                        var_dump($row);
                        $import = new CsvImport();
                        $import->setFirstname($row[1]);
                        $import->setLastname($row[2]);
                        $import->setInitial($row[3]);
                        $import->setAge($row[5]);
                        $import->setDob(\DateTime::createFromFormat('m-d-Y', $row[4]));

                        $this->em->persist($import);
                        if (($i % $batchSize) === 0) {
                            $this->em->flush();
                            $this->em->clear(); // Detaches all objects from Doctrine!
                        }
                        $i++;
                    }
                    $this->em->flush();
                    $this->em->clear(); // Detaches all objects from Doctrine!
                }
            }

        }

        return $this->render('test2.html.twig', ['form' => $form->createView()] );
    }
}
