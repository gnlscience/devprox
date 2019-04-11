<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\People;
use App\Service\PeopleService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Form\Extension\Core\Type\FileType;



class Test2Controller extends AbstractController
{
    private $peopleService;

    public function __construct(PeopleService $peopleService)
    {
        $this->peopleService = $peopleService;
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

        return $this->render('test2.html.twig', ['form' => $form->createView()] );
    }
}
