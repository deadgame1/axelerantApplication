<?php

namespace App\Controller;

use App\Entity\Feed;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\FeedRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class FeedController extends AbstractController
{
    private $feedRepo;
    private $em;

    public function __construct(FeedRepository $feedRepo, EntityManagerInterface $em)
    {
        $this->feedRepo = $feedRepo;
        $this->em = $em;
    }
    /**
     * @Route("/feedList", name="feedList")
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $feedUrl = $this->feedRepo->findAll();
        return $this->render('feedList.html.twig', [
            'feedList' => $feedUrl,
        ]);
    }

    /**
     * @Route("/editFeed/{feedId}", name="editFeed")
     */
    public function editFeed(int $feedId = 0){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if($feedId !== 0){ //updating existing feed
            $feedContent = $this->feedRepo->findOneBy(['id' => $feedId]);
            return $this->render('editFeed.html.twig', [
                'id' => $feedContent->getId(),
                'url' => $feedContent->getUrl(),
            ]);
        }else{//adding new feed
            return $this->render('editFeed.html.twig', [
                'id' => 0,
                'url' => '',
            ]);
        }
    }

    /**
     * @Route("/saveFeed/{feedId}", name="saveFeed")
     */
    public function saveFeed(Request $request, int $feedId = 0){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if(!$request->get('feedUrl')){
            return $this->redirectToRoute('feedList');
        }

        if($feedId !== 0){ //updating existing feed
            $feedContent = $this->feedRepo->findOneBy(['id' => $feedId]);
            $feedContent->setUrl($request->get('feedUrl'));

            $this->em->persist($feedContent);
        }else{ //adding new feed
            $newFeed = new Feed();
            $newFeed->setUrl($request->get('feedUrl'));

            $this->em->persist($newFeed);
        }
        
        $this->em->flush();

        return $this->redirectToRoute('feedList');
    }

    /**
     * @Route("/deleteFeed/{feedId}", name="deleteFeed")
     */
    public function deleteFeed(Request $request, int $feedId){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $feedContent = $this->feedRepo->findOneBy(['id' => $feedId]);
        if(!$feedContent){
            return $this->redirectToRoute('feedList');
        }

        $this->em->remove($feedContent);
        $this->em->flush();
        return $this->redirectToRoute('feedList');
    }

    /**
     * @Route("/viewFeed", name="viewFeed")
     */
    public function viewFeedContent(Request $request){
        $source = $request->get('feedUrl');
        if(!$source){
            return $this->redirectToRoute('feedList');
        }

        if(@simplexml_load_file($source)){
            $content = simplexml_load_file($source);
            return $this->render('feedContent.html.twig', [
                'error' => false,
                'contentList' => $content->channel->item
            ]);
        }else{
            return $this->render('feedContent.html.twig', [
                'error' => true,
            ]);
        }
    }
}
