<?php

namespace Jan\ZadanieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Jan\ZadanieBundle\Entity\Items;

class RestController extends Controller
{    
    public function getAllAction(Request $request, $_format){
        $repository = $this->getDoctrine()
        ->getRepository(Items::class);

        $items = $repository->findByAmountFilter([
            'gtamount' => $request->query->get('gtamount'),
            'amount' => $request->query->get('amount')
        ]);

        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array(new XmlEncoder(), new JsonEncoder()));
        $res = $serializer->serialize($items, $_format);

        $response = new Response($res,200);
        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response;
    }
    public function getAction($_format, $id){
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository(Items::class)->find($id);
        if (!$item instanceof Items) {
            throw new HttpException(400, "Nie znaleziono rekordu id = ".$id);            
        }

        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array(new XmlEncoder(), new JsonEncoder()));
        $res = $serializer->serialize($item, $_format);

        $response = new Response($res,200);
        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response;
    }
    public function postAction(Request $request, $_format){
        $item = new Items();
        $item->setName($request->query->get('name'));
        $item->setAmount($request->query->get('amount'));

        $validator = $this->get('validator');
        $errors = $validator->validate($item);
        if (count($errors) > 0) {
            /*foreach ($errors as $error) {
                echo $error->getMessage().'<br>';
            }*/            
            throw new HttpException(400, $errors);
        }

        $em = $this->getDoctrine()->getManager();   
        $em->persist($item);
        $em->flush();

        $data = array(
            'status' => 'success',
            'message' => 'Utworzono rekord'
        );

        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array(new XmlEncoder(), new JsonEncoder()));
        $res = $serializer->serialize($data, $_format);

        $response = new Response($res,200);
        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response;
    }
    public function putAction(Request $request, $_format, $id){        
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository(Items::class)->find($id);
        if (!$item instanceof Items) {
            throw new HttpException(400, "Nie znaleziono rekordu id = ".$id);            
        }
        $item->setName($request->query->get('name'));
        $item->setAmount($request->query->get('amount'));

        $validator = $this->get('validator');
        $errors = $validator->validate($item);
        if (count($errors) > 0) {
            throw new HttpException(400, $errors);
        }

        $em->flush();
        $data = array(
            'status' => 'success',
            'message' => 'Zaktualizowano rekord id = '.$id
        );

        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array(new XmlEncoder(), new JsonEncoder()));
        $res = $serializer->serialize($data, $_format);

        $response = new Response($res,200);
        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response;
    }
    public function deleteAction($_format, $id){
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository(Items::class)->find($id);
        if (!$item) {
            throw new HttpException(400, "Nie znaleziono rekordu id = ".$id);
        }
        $em->remove($item);
        $em->flush();

        $data = array(
            'status' => 'success',
            'message' => 'Usuniety rekord id = '.$id
        );

        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array(new XmlEncoder(), new JsonEncoder()));
        $res = $serializer->serialize($data, $_format);

        $response = new Response($res,200);
        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response;
    }
}