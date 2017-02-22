<?php
require_once "../../vendor/autoload.php";
use App\Admin\Room;
use App\Core\Session;
use App\Core\Redirect;
use App\Core\Input;
use App\Core\Validation;
//......................
// String convert to 24 hours time ..........
// ..................................
$timeFrom = Input::get('timeFrom');
$timeTo = Input::get('timeTo');

$timeFromHour = substr($timeFrom, 0, 2);
$timeFromMinuts = substr($timeFrom, 3, 2);
$timeFromMeridiem = substr($timeFrom, -2, 2);
if ($timeFromMeridiem == 'am') {
    if ($timeFromHour == 12) {
        $timeFrom = '00' . ':' . $timeFromMinuts;
    }else {
        $timeFrom = $timeFromHour . ':' . $timeFromMinuts;
    }
}elseif ($timeFromMeridiem == 'pm') {
    $timeFromHourCmpr = $timeFromHour - 12;
    if ($timeFromHourCmpr > 0) {
        $timeFromHour + $timeFromHourCmpr;
        $timeFrom = $timeFromHour . ':' . $timeFromMinuts;
    }else {
        $timeFrom = $timeFromHour . ':' . $timeFromMinuts;
    }
}

$timeToHour = substr($timeTo, 0, 2);
$timeToMinuts = substr($timeTo, 3, 2);
$timeToMeridiem = substr($timeTo, -2, 2);
if ($timeToMeridiem == 'am') {
    if ($timeToHour == 12) {
        $timeTo = '00' . ':' . $timeToMinuts;
    }else {
        $timeTo = $timeToHour . ':' . $timeToMinuts;
    }
}elseif ($timeToMeridiem == 'pm') {
    $timeToHourCmpr = $timeToHour - 12;
    if ($timeToHourCmpr > 0) {
        $timeToHour + $timeToHourCmpr;
        $timeTo = $timeToHour . ':' . $timeToMinuts;
    }else {
        $timeTo = $timeToHour . ':' . $timeToMinuts;
    }
}
// $_POST['timeFrom'] = $timeFrom;
// $_POST['timeTo'] = $timeTo;


// allocation action start here.........
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if (Input::exists('post')){
        if (Validation::valid(Input::get('department')) != '.....Select Department.....' && Validation::valid(Input::get('room')) != '.....Select Room No.....' && Validation::valid(Input::get('day')) != '.....Select Day.....'){
            $_POST['timeFrom'] = $timeFrom;
            $_POST['timeTo'] = $timeTo;
            if (Room::checkUniqueAllocateDay(Input::get('day')) || Room::checkUniqueAllocateRoom(Input::get('room'))){
                $allocation = new Room();
                if ($allocation->setDepartmentData($_POST)->storeRoomAllocation()){
                    Session::put('success', 'Successfully allocated!');
                    Redirect::to('room_allocation.php');
                }else{
                    Session::put('error', 'Something going wrong !');
                    Redirect::to('room_allocation.php');
                }
            }else{
                $allocateDayInfo = Room::getAllocateDayData();
                $inputTimeFrom = Input::get('timeFrom');
                $inputTimeTo = Input::get('timeTo');
                $overlapCount = 0;
                $roomCount = 0;

                foreach ($allocateDayInfo as $singleDay){
                    if ($singleDay->alocation_room == Input::get('room')){
                        $roomCount = 1;
                        if (strtotime($inputTimeFrom) < strtotime($singleDay->alocation_time_from)){
                            if (strtotime($inputTimeTo) < strtotime($singleDay->alocation_time_from)){
                                // OK.
                            }else{
                                $overlapCount++;
                                // not OK.
                            } // 2nd time check end.
                        }else{
                            if (strtotime($inputTimeFrom) > strtotime($singleDay->alocation_time_to)){
                                // ok.
                            }else{
                                $overlapCount++;
                                // not OK.
                            } // 3rd time check end...
                        } // first time check end...
                    } // room check end .......
                } // foreach end ......
                if ($roomCount === 0){
                    $allocation = new Room();
                    if ($allocation->setDepartmentData($_POST)->storeRoomAllocation()){
                        Session::put('success', 'Successfully allocated!');
                        Redirect::to('room_allocation.php');
                    }else{
                        Session::put('error', 'Something going wrong !');
                        Redirect::to('room_allocation.php');
                    }
                }elseif ($overlapCount === 0){
                    $allocation = new Room();
                    if ($allocation->setDepartmentData($_POST)->storeRoomAllocation()){
                        Session::put('success', 'Successfully allocated!');
                        Redirect::to('room_allocation.php');
                    }else{
                        Session::put('error', 'Something going wrong !');
                        Redirect::to('room_allocation.php');
                    }
                }else{
                    Session::put('error', 'Sorry already allocated the room for this time !');
                    Redirect::to('room_allocation.php');
                }
            } // day & room unique checking end......

        }else{
            Session::put('error', 'Invalid Input!');
            Redirect::to('room_allocation.php');
        }
    }else{
        Session::put('error', 'Fields are required');
        Redirect::to('room_allocation.php');
    }
}else{
    Redirect::to('room_allocation.php');
}