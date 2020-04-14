<?php
// accsessed by all
header('Access-Control-Allow-Origin: *');
// output must be json object
header('Content-Type: application/json');
// method must be post
header('Access-Control-Allow-Methods: POST');
// set allowed headers
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods');


function covid19ImpactEstimator($data, $impact, $severeImpact)
{
  // determine the number of days
  $realTime = (int)$data->timeToElapse;
  $data->timeToElapse = determineOfNumberDays($data->periodType, (int)$data->timeToElapse);

  // determine the number of currently infected people
  $impact['currentlyInfected'] = currentlyInfected((int)$data->reportedCases, 10);
  $severeImpact['currentlyInfected'] = currentlyInfected((int)$data->reportedCases, 50);

  // determine the infections by requested time
  $impact['infectionsByRequestedTime'] = infectionsByRequestedTime($impact, (int)$data->timeToElapse);
  $severeImpact['infectionsByRequestedTime'] = infectionsByRequestedTime($severeImpact, (int)$data->timeToElapse);

  // determine the severe cases by requested time
  $impact['serveCasesByRequestedtime'] = serveCasesByRequestedtime($impact);
  $severeImpact['serveCasesByRequestedtime'] = serveCasesByRequestedtime($severeImpact);

  // determine the number of hospital bed space
  $impact['hospitalBedsRequetedTime'] = hospitalBedsRequetedTime($impact, (int)$data->totalHospitalBeds);
  $severeImpact['hospitalBedsRequetedTime'] = hospitalBedsRequetedTime($severeImpact, (int)$data->totalHospitalBeds);

  // determine the ICU care
  $impact['caseForICURequestedTime'] = caseForICURequestedTime($impact);
  $severeImpact['caseForICURequestedTime'] = caseForICURequestedTime($severeImpact);

  // determin the casesForVentilatorsByRequestedTime
  $impact['casesForVentilatorsByRequestedTime'] = casesForVentilatorsByRequestedTime($impact);
  $severeImpact['casesForVentilatorsByRequestedTime'] = casesForVentilatorsByRequestedTime($severeImpact);

  // determine money lost over time
  $impact['dollarsInflight'] = dollarsInFlight($impact, $impact['currentlyInfected'], $data->region->avgDailyIncomePopulation, $data->timeToElapse);
  $severeImpact['dollarsInflight'] = dollarsInFlight($severeImpact, $severeImpact['currentlyInfected'], $data->region->avgDailyIncomePopulation, $data->timeToElapse);

  $data->timeToElapse = $realTime;

  output($data, $impact, $severeImpact);
}


function currentlyInfected($reportedCases, $percentage)
{
  return $reportedCases * $percentage;
}

function infectionsByRequestedTime($type, $numberOfDays)
{
  return $type['currentlyInfected'] * pow(2, floor($numberOfDays / 3));
}

function serveCasesByRequestedtime($type)
{
  return floor($type['infectionsByRequestedTime'] * 0.15);
}

function hospitalBedsRequetedTime($type, $totalHospitalBeds)
{
  $hospitalBedsRequestedTime = (0.35 * $totalHospitalBeds) - $type['serveCasesByRequestedtime'];
  return floor($hospitalBedsRequestedTime);
}

function caseForICURequestedTime($type)
{
  return floor($type['infectionsByRequestedTime'] * 0.05);
}

function casesForVentilatorsByRequestedTime($type)
{
  return floor($type['infectionsByRequestedTime'] * 0.02);
}

function dollarsInFlight($type, $numberOfInfectedPeople, $averageDailyIncome, $numberOfDays)
{
  $dollarsInFlight = $type['infectionsByRequestedTime'] * $numberOfInfectedPeople * $averageDailyIncome * $numberOfDays;
  return floor($dollarsInFlight);
}

function output($data, $impact, $severeImpact)
{
  echo json_encode(
    ["data" => $data, "impact" => $impact, "severeImpact" => $severeImpact]
  );
}

function determineOfNumberDays($periodOfTime, $timeToElapse)
{
  switch ($periodOfTime) {
    case 'months':
      $timeToElapse = $timeToElapse * 30;
      break;

    case 'weeks':
      $timeToElapse = $timeToElapse * 7;
      break;

    default:
      $timeToElapse = $timeToElapse;
      break;
  }
  return $timeToElapse;
}
