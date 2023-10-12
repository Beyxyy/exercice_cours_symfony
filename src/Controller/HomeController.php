<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\BookingType;
use App\Repository\SellerRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(SellerRepository $sellerRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'sellers' => $sellerRepository->findAll(),
        ]);
    }

    #[Route('/booking/{id}/{week}', name: 'app_seller_booking', defaults: ["week" => null], methods: ['GET', 'POST'])]
    public function booking(SellerRepository $sellerRepository, $id, ?int $week, EntityManagerInterface $entityManager, Request $request): Response
    {

        $currentWeek = $week ?? date('W');//je récupère la current week si elle est en parametre sinon je la récupère avec la methode date
        $seller = $sellerRepository->findOneBy(['id' => $id]);
        $bookings = $seller->getBookings();
        $bookedSlots = [];

        foreach ($bookings as $booking) {
            if ($booking->getWeek() == $currentWeek) {
                $bookedSlots[$booking->getDay()][$booking->getTime()] = true;
            }
        }

        $user = $this->getUser();

        $booking = new Booking();
        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $day = $form->get('day')->getData();
            $time = $form->get('time')->getData();

            $booking
                ->setDay($day)
                ->setTime($time)
                ->setCliebt($user)
                ->setSeller($seller)
                ->setWeek($currentWeek);

            $entityManager->persist($booking);
            $entityManager->flush();

            return $this->redirectToRoute('app_seller_booking', ['id' => $seller->getId(), 'week' => $currentWeek]);
        }

        $duration = $seller->getDuration() ?? 30;
        $startTime = new DateTimeImmutable('9:00');
        $endTime = new DateTimeImmutable('18:00');

        $timeSlots = [];
        while ($startTime < $endTime) {
            $timeSlots[] = $startTime->format('h:i');
            $startTime = $startTime->modify("+$duration minutes");
        }

        $weekSlots = [
            'times' => $timeSlots,
            'days' => ['MOnday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']
        ];

        $dt = new DateTimeImmutable();
        $dt = $dt->setISODate(date('Y'), $currentWeek);

        setlocale(LC_TIME, 'fr_FR', 'fra');

        $weekDates = [];
        for ($i = 0; $i < 5; $i++) {
            $date = new DateTimeImmutable();
            $date = $date->setISODate((int)$date->format('o'), $currentWeek, $i + 1);
            $weekDates[] = strftime("%A %d/%m/%Y", $date->getTimestamp());
        }

        return $this->render('home/booking.html.twig', [
            'seller' => $seller,
            'booking' => $bookings,
            'weekSlots' => $weekSlots,
            'bookedSlots' => $bookedSlots,
            'form' => $form->createView(),
            'currentWeek' => $currentWeek,
            'weekDates' => $weekDates,
        ]);
    }
}
