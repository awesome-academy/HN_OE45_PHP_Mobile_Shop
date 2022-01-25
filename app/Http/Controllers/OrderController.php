<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\OrderDetailRepository;
use App\Contracts\Repositories\OrderRepository;
use App\Contracts\Repositories\UserRepository;
use App\Http\Requests\Order\StoreRequest;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Mail\OrderUser;
use App\Models\Order;
use App\Notifications\OrderAdminNotification;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class OrderController extends Controller
{
    public $orderRepository;
    public $orderDetailRepository;
    public $userRepository;

    public function __construct(
        OrderRepository $orderRepository,
        OrderDetailRepository $orderDetailRepository,
        UserRepository $userRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderDetailRepository = $orderDetailRepository;
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        $orders = $this->orderRepository->getAllOrders();

        return view('admin.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $orderDetails = $this->orderDetailRepository->showDetailsOrders($id);

        return view('admin.orders.details', compact('orderDetails'));
    }

    public function getOrderPending()
    {
        $orders = $this->orderRepository->getAllOrderPending();

        return view('user.orders', compact('orders'));
    }

    public function getOrderUser()
    {
        $orders = $this->orderRepository->getOrderUserApprove();

        return view('user.history_order', compact('orders'));
    }

    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();

            $cart = Session::get('cart');
            $amount = config('const.active');
            foreach ($cart as $value) {
                $amount += $value['price'] * $value['quantity'];
            }

            $data = $request->only(['user_id', 'phone', 'address']);
            $data['amount'] = $amount;
            $data['status'] = config('const.pending');

            $order = $this->orderRepository->create($data);

            foreach ($cart as $value) {
                $this->orderDetailRepository->create(
                    [
                        'order_id' => $order->id,
                        'product_id' => $value['id'],
                        'color_id' => $value['color'],
                        'memory_id' => $value['memory'],
                        'price' => $value['price'],
                        'quantity' => $value['quantity'],
                        'image' => $value['image'],
                    ]
                );
            }

            Session::forget('cart');

            $admin = $this->userRepository->findAdmin();

            $orderData = [
                'order_id' => $order->id,
                'content' => $order->user->name,
            ];

            // send notify admin
            $admin->notify(new OrderAdminNotification($orderData));

            DB::commit();

            return redirect()->route('home');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
        }
    }

    public function state($id)
    {
        try {
            DB::beginTransaction();

            $order = $this->orderRepository->findOrFail($id);
            $orderDetails = $this->orderDetailRepository->showDetailsOrders($id);

            foreach ($orderDetails as $item) {
                $productAttr = $item->product->productAttributes
                    ->where('color_id', $item->color_id)
                    ->where('memory_id', $item->memory_id)
                    ->firstOrFail();
                if ($item->quantity > $productAttr->quantity) {
                    return redirect()->route('admin.orders.index')->with('alert', __('common.fail_quantity'));
                }

                $remaining = $productAttr->quantity - $item->quantity;
                $productAttr->update(
                    [
                        'quantity' => $remaining,
                    ]
                );
            }

            $this->orderRepository->whereId($id)->update(
                [
                    'status' => config('const.approve'),
                ]
            );

            //send mail to user after admin approve order
            Mail::to($order->user->email)
                ->send(new OrderUser($order));

            DB::commit();

            return redirect()->route('admin.orders.index');
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex);
        }
    }

    public function rejectOrder($id)
    {
        $this->orderRepository->whereId($id)->update(
            [
                'status' => config('const.reject'),
            ]
        );

        return redirect()->route('admin.orders.index');
    }

    public function destroy(Order $order)
    {
        try {
            DB::beginTransaction();

            $order->delete();
            $order->orderDetails()->delete();

            DB::commit();

            return redirect()->back();
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex);
        }
    }

    public function getApiAllOrder()
    {
        if (Auth::user()->tokenCan('admin:view')) {
            $orders = $this->orderRepository->getAllOrders();

            return new OrderCollection($orders, __FUNCTION__);
        }

        return new OrderCollection([], __FUNCTION__, 'Unauthorize');
    }

    public function getApiDetailsOrder($id)
    {
        $orderDetails = $this->orderDetailRepository->showDetailsOrders($id);
        $user = Auth::user();
        if ($user->tokenCan('admin:view') || $user->id == $orderDetails[0]->order->user_id) {
            return new OrderResource($orderDetails, __FUNCTION__);
        }

        return new OrderResource([], __FUNCTION__, 'Unauthorize');
    }
}
