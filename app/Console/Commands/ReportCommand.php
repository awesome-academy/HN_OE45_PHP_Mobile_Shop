<?php

namespace App\Console\Commands;

use App\Contracts\Repositories\OrderDetailRepository;
use App\Contracts\Repositories\OrderRepository;
use App\Contracts\Repositories\UserRepository;
use App\Mail\ReportSales;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:sales';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sales report at 8:00am on the last day of the month.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(
        OrderRepository $orderRepository,
        OrderDetailRepository $orderDetailRepository,
        UserRepository $userRepository
    ) {
        $report = $orderRepository->getOrderApproveOfMonth();
     
        Mail::to($userRepository->findAdmin())->send(new ReportSales($report));

        return Command::SUCCESS;
    }
}
