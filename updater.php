<?php
include 'connections.php'; 

if (!isset($mysqli) || $mysqli->connect_error) {
    die("Database connection failed. Please check 'connections.php'. Error: " . ($mysqli->connect_error ?? "Connection object missing."));
}

$department = "civil"; 
$upload_dir = 'uploads/'; 

$equipment_data = [
    [ 'equipment_name' => 'Binocular Microscope', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . 'Microscope_10282.jpg', 'specification' => 'Model No. 10282, Max Rockwell Correction II', 'description' => 'Microscope for material science analysis.', 'purpose' => 'Material analysis and research.', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => 'Unknown', 'mmd_no' => 12349, 'supplier' => 'Sushruta Instruments, I.M.A.N', 'amount' => 21000.00, 'fund' => 'Departmental Fund', 'incharge' => 'Civil Dept. Faculty', ],
    [ 'equipment_name' => 'Software - IT/OS Class License', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . 'ITOS_Software.png', 'specification' => 'IT/OS Class License, CD 33', 'description' => 'Operating system/IT software license for classroom use.', 'purpose' => 'Computer lab teaching and projects.', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => 'Unknown', 'mmd_no' => 12350, 'supplier' => 'M/S. Citilabs', 'amount' => 200000.00, 'fund' => 'IT Infrastructure Grant', 'incharge' => 'Civil Dept. Faculty', ],
    [ 'equipment_name' => 'Leica Disto D510 Handheld Laser Meter', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . 'Leica_Disto.jpeg', 'specification' => 'Disto D510 Model, Handheld', 'description' => 'Digital laser distance measuring device.', 'purpose' => 'Surveying and site measurement.', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => 'Unknown', 'mmd_no' => 12351, 'supplier' => 'Indus Industrial, Chennai', 'amount' => 55022.00, 'fund' => 'Lab Modernization Fund', 'incharge' => 'Civil Dept. Faculty', ],
    [ 'equipment_name' => '73A Adapter', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . '73A_Adapter.jpg', 'specification' => 'No S.L. No./Leica Meter Adapter', 'description' => 'Adapter for Leica measuring equipment.', 'purpose' => 'Accessory for surveying equipment.', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => 'Unknown', 'mmd_no' => 12352, 'supplier' => 'Indus Industrial, Chennai', 'amount' => 0.00, 'fund' => 'Lab Modernization Fund', 'incharge' => 'Civil Dept. Faculty', ],
    [ 'equipment_name' => 'Leica TRI 100 Tripod', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . 'Leica_Tripod.jpeg', 'specification' => 'TRI 100 model', 'description' => 'Tripod stand for laser meter/surveying equipment.', 'purpose' => 'Support for surveying equipment.', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => 'Unknown', 'mmd_no' => 12353, 'supplier' => 'Indus Industrial, Chennai', 'amount' => 0.00, 'fund' => 'Lab Modernization Fund', 'incharge' => 'Civil Dept. Faculty', ],
    [ 'equipment_name' => 'Automated Driver Vision Scanner', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . 'Vision_Scanner.jpg', 'specification' => 'Model EMP 115', 'description' => 'Equipment for studying and scanning driver vision metrics.', 'purpose' => 'Traffic and Transportation Engineering Lab.', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => 'Unknown', 'mmd_no' => 12354, 'supplier' => 'Warwick Evans Optical Co. Ltd.', 'amount' => 190000.00, 'fund' => 'Research Project Fund', 'incharge' => 'Civil Dept. Faculty', ],
    [ 'equipment_name' => 'TransCAD Transportation Software', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . 'TransCAD_Software.png', 'specification' => '9 Licenses, S/N: Set 1', 'description' => 'Professional software for transportation planning and modelling.', 'purpose' => 'Transportation Engineering coursework and research.', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => '2004', 'mmd_no' => 12355, 'supplier' => 'M/S. Caliper Corporation, U.S.A.', 'amount' => 407000.00, 'fund' => 'Plan Fund', 'incharge' => 'Civil Dept. Faculty', ],
    [ 'equipment_name' => 'Driver Vision Scanner Head Unit', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . 'Scanner_Head_Unit.jpg', 'specification' => 'S/N: E092/03', 'description' => 'A head unit component for the driver vision scanner.', 'purpose' => 'Traffic and Transportation Engineering Lab.', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => '2012', 'mmd_no' => 12356, 'supplier' => 'Warwick Evans Optical Co. Ltd.', 'amount' => 196340.00, 'fund' => 'Research Project Fund', 'incharge' => 'Civil Dept. Faculty', ],
    [ 'equipment_name' => 'Driving Simulator', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . 'Driving_Simulator.jpeg', 'specification' => '1 Unit, S/N: E01/03', 'description' => 'Full-scale driving simulator for research and testing.', 'purpose' => 'Transportation safety research and human factors study.', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => '2015', 'mmd_no' => 12357, 'supplier' => 'Visioite Simulation & Training Pvt. Ltd.', 'amount' => 352000.00, 'fund' => 'Project Grant', 'incharge' => 'Civil Dept. Faculty', ],
    [ 'equipment_name' => 'Captain Civil Missile 1 Unit', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . 'Missile_Unit.jpg', 'specification' => 'S/N: 3392', 'description' => 'Likely a specialized unit for civil engineering/surveying (e.g., a data logger or sensor).', 'purpose' => 'Civil surveying and data acquisition.', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => 'Unknown', 'mmd_no' => 12358, 'supplier' => 'M/S Global Trading Exporter', 'amount' => 5650.00, 'fund' => 'Departmental Fund', 'incharge' => 'Civil Dept. Faculty', ],
    [ 'equipment_name' => 'Mimo Table 18', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . 'Mimo_Table.jpeg', 'specification' => 'Mimo Table 18, Order No. 0002014', 'description' => 'Specialized table or stand for lab equipment.', 'purpose' => 'Lab setup and equipment support.', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => '2015', 'mmd_no' => 12359, 'supplier' => 'ATS Sales International, New Delhi', 'amount' => 122400.00, 'fund' => 'Departmental Fund', 'incharge' => 'Civil Dept. Faculty', ],
    [ 'equipment_name' => 'Wohan VBox-20Hz GPS Data Logger', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . 'Wohan_VBox.jpeg', 'specification' => 'VBox-20Hz, 1 No., S/N: 688393', 'description' => 'GPS data logging system for traffic/vehicle dynamics studies.', 'purpose' => 'Traffic Engineering/Vehicle Dynamics Research.', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => '2016', 'mmd_no' => 12360, 'supplier' => 'M/S Racelogic Ltd., Musafganj', 'amount' => 0.00, 'fund' => 'Research Project Fund', 'incharge' => 'Civil Dept. Faculty', ],
    [ 'equipment_name' => 'Traffic Conflict Video Speed Recording System', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . 'Traffic_Video.jpeg', 'specification' => 'Mercury Digital Camera with tripod, lens, storage.', 'description' => 'High-speed video system for capturing and analyzing traffic conflicts.', 'purpose' => 'Traffic safety research.', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => '2012', 'mmd_no' => 12361, 'supplier' => 'Turbo Consulting (India) Pvt. Ltd.', 'amount' => 287520.00, 'fund' => 'Project Fund', 'incharge' => 'Civil Dept. Faculty', ],
    [ 'equipment_name' => 'Thermal Imager Camera', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . 'Thermal_Imager.jpeg', 'specification' => 'Fluke TiR1, 75Hz/9Hz, S/N: 30209782', 'description' => 'Infrared camera for non-destructive thermal analysis of structures.', 'purpose' => 'Non-Destructive Testing (NDT) of materials/structures.', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => 'Unknown', 'mmd_no' => 12362, 'supplier' => 'V. J. T. R. O. N. I. C. S. (India), Mumbai', 'amount' => 560000.00, 'fund' => 'Plan Fund', 'incharge' => 'Civil Dept. Faculty', ],
    [ 'equipment_name' => 'Biomechanics & Gait Analysis System', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . 'Gait_Analysis.jpeg', 'specification' => 'Data logger: Leica W.N. 09-112, with software and interface cable.', 'description' => 'System for studying human movement and gait patterns.', 'purpose' => 'Ergonomics and Transportation research.', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => '2016', 'mmd_no' => 12363, 'supplier' => 'M/S C. V. International, Sector-9, Rohini, Delhi', 'amount' => 210000.00, 'fund' => 'Project Fund', 'incharge' => 'Civil Dept. Faculty', ],
    [ 'equipment_name' => 'Electromyogram (EMG)', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . 'Electromyogram_EMG.jpeg', 'specification' => '1.00 Unit, Order No. 490000', 'description' => 'Device for measuring electrical activity produced by skeletal muscles.', 'purpose' => 'Ergonomics/Human Factors research.', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => 'Unknown', 'mmd_no' => 12364, 'supplier' => 'GEMTECH Marketing & Distribution Pvt. Ltd.', 'amount' => 835000.00, 'fund' => 'Project Fund', 'incharge' => 'Civil Dept. Faculty', ],
    [ 'equipment_name' => 'Vicon Video 10 Hz GPS Data Logger System', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . 'Vicon_Video.jpeg', 'specification' => 'Vicon Video 10 Hz GPS Data Logger System, 1.00 Unit', 'description' => 'GPS and video data logger for advanced tracking and analysis.', 'purpose' => 'Traffic and Pedestrian studies.', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => 'Unknown', 'mmd_no' => 12365, 'supplier' => 'Vinitrans Mobility Solutions', 'amount' => 109896.80, 'fund' => 'Research Project Fund', 'incharge' => 'Civil Dept. Faculty', ],
    [ 'equipment_name' => 'Vicon V-Pad with peripherals', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . 'Vicon_VPad.jpeg', 'specification' => 'PTV Vicon V-Pad with peripherals, 1.00 Unit', 'description' => 'Tablet or data collection pad for the Vicon system.', 'purpose' => 'Data acquisition accessory.', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => 'Unknown', 'mmd_no' => 12366, 'supplier' => 'Vinitrans Mobility Solutions', 'amount' => 2929699.00, 'fund' => 'Research Project Fund', 'incharge' => 'Civil Dept. Faculty', ],
    [ 'equipment_name' => 'Retracted Plastic Film for Reflective Surface', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . 'Plastic_Film.jpeg', 'specification' => 'S/N: 55/IMP/07, 1.00 Roll, 120cm width, 1200cm length.', 'description' => 'Special plastic film for creating reflective testing surfaces.', 'purpose' => 'Pavement or material testing.', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => 'Unknown', 'mmd_no' => 12367, 'supplier' => 'Unknown/Missing', 'amount' => 0.00, 'fund' => 'Lab Consumables Fund', 'incharge' => 'Civil Dept. Faculty', ],
    [ 'equipment_name' => 'Rockwell Spread Guage', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . 'Spread_Guage.jpeg', 'specification' => '4 Units', 'description' => 'Gauge used for measuring the spread of materials (e.g., concrete).', 'purpose' => 'Concrete/Material Testing Lab.', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => '2014', 'mmd_no' => 12368, 'supplier' => 'Sahara Trading Corp.', 'amount' => 10320.00, 'fund' => 'Lab Upgrade Fund', 'incharge' => 'Civil Dept. Faculty', ],
    [ 'equipment_name' => 'Actioncam Device', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . 'Actioncam_Device.jpeg', 'specification' => 'Actioncam Device - A530, with antenna, clock, and charging/communication kit.', 'description' => 'Compact action camera system used for field data collection and video logging.', 'purpose' => 'Traffic video capture and field surveying.', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => '2015', 'mmd_no' => 12369, 'supplier' => 'TIBEK Technologies, Range-11010', 'amount' => 159898.50, 'fund' => 'Project Fund', 'incharge' => 'Civil Dept. Faculty', ],
    [ 'equipment_name' => '32 Channel Wearable EEG', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . 'Wearable_EEG.jpeg', 'specification' => '32 Channel Wearable EEG, 1.00 Unit, with 3-pin Cap Starter.', 'description' => 'Portable Electroencephalogram system for brain activity measurement in field settings.', 'purpose' => 'Driver behavior and cognitive load studies (Human Factors research).', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => 'Unknown', 'mmd_no' => 12370, 'supplier' => 'GEMTECH Marketing & Distribution Pvt. Ltd.', 'amount' => 355000.00, 'fund' => 'Project Grant', 'incharge' => 'Civil Dept. Faculty', ],
    [ 'equipment_name' => 'V-Pads with peripherals', 'equipment_dept' => 'civil', 'photo_path' => $upload_dir . 'V-Pads_Peripheral.jpeg', 'specification' => 'V-Pads with necessary peripherals (Assuming related to Vicon system).', 'description' => 'Data collection pads/tablets for Vicon system (Placeholder based on related items).', 'purpose' => 'Data acquisition accessory for Vicon system.', 'users' => 'Researcher, Students, Faculty', 'year_of_purchase' => '2016', 'mmd_no' => 12371, 'supplier' => 'Vinitrans Mobility Solutions', 'amount' => 50000.00, 'fund' => 'Research Project Fund', 'incharge' => 'Civil Dept. Faculty', ],
];
 
$query = "INSERT INTO $department 
    (equipment_name, equipment_dept, photo, specification, description, purpose, users, 
    availability, currently_used_by, last_used_by,
    year_of_purchase, mmd_no, supplier, amount, fund, incharge) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$success_count = 0;
$failure_count = 0;

if ($stmt = $mysqli->prepare($query)) {
    
    $equipment_name = $equipment_dept = $photo = $specification = $description = $purpose = $users = "";
    $availability = $currently_used_by = $last_used_by = $year_of_purchase = $supplier = $fund = $incharge = "";
    $mmd_no = 0;
    $amount = 0.0;
    
    $stmt->bind_param(
        "sssssssssssissss",
        $equipment_name,
        $equipment_dept,
        $photo,
        $specification,
        $description,
        $purpose,
        $users,
        $availability,
        $currently_used_by,
        $last_used_by,
        $year_of_purchase,
        $mmd_no,
        $supplier,
        $amount, 
        $fund,
        $incharge
    );

    echo "<h2>Starting Bulk Insert for " . count($equipment_data) . " Records...</h2>";

    foreach ($equipment_data as $record) {
        $availability = 'Available';
        $currently_used_by = null; 
        $last_used_by = null;

        $equipment_name     = $record['equipment_name'];
        $equipment_dept     = $record['equipment_dept'];
        $photo              = $record['photo_path']; 
        $specification      = $record['specification'];
        $description        = $record['description'];
        $purpose            = $record['purpose'];
        $users              = $record['users'];
        $year_of_purchase   = $record['year_of_purchase'];
        $mmd_no             = intval($record['mmd_no']);     
        $supplier           = $record['supplier'];
        $amount             = floatval($record['amount']);   
        $fund               = $record['fund'];
        $incharge           = $record['incharge'];

        if ($stmt->execute()) {
            echo "<p style='color:green;'>SUCCESS: Added **{$equipment_name}** (MMD: {$mmd_no}).</p>";
            $success_count++;
        } else {
            echo "<p style='color:red;'>FAILURE: Could not add **{$equipment_name}** (MMD: {$mmd_no}). Error: " . $stmt->error . "</p>";
            $failure_count++;
        }
    }

    $stmt->close();
} else {
    echo "<h1>Critical Error</h1>";
    echo "<p style='color:red;'>Error in preparing statement for bulk insert: " . $mysqli->error . "</p>";
}

echo "<hr><h3>Bulk Insert Summary</h3>";
echo "<p>Total Records Attempted: " . count($equipment_data) . "</p>";
echo "<p style='color:green;'>Successful Inserts: {$success_count}</p>";
echo "<p style='color:red;'>Failed Inserts: {$failure_count}</p>";

$mysqli->close();

?>
