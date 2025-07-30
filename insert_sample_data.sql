-- Insert sample admin user for testing
INSERT INTO users (username, password, department) VALUES 
('admin', 'admin123', 'civil'),
('admin_mech', 'admin123', 'mechanical'),
('admin_elec', 'admin123', 'electrical'),
('admin_cs', 'admin123', 'computer_science'),
('admin_chem', 'admin123', 'chemistry'),
('admin_phy', 'admin123', 'physics'),
('admin_math', 'admin123', 'mathematics'),
('admin_bio', 'admin123', 'biology');

-- Insert sample user details
INSERT INTO userdetails (username, name, email, department, password) VALUES 
('admin', 'Administrator', 'admin@iitb.ac.in', 'civil', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('admin_mech', 'Mechanical Admin', 'admin_mech@iitb.ac.in', 'mechanical', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('admin_elec', 'Electrical Admin', 'admin_elec@iitb.ac.in', 'electrical', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('admin_cs', 'CS Admin', 'admin_cs@iitb.ac.in', 'computer_science', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('admin_chem', 'Chemistry Admin', 'admin_chem@iitb.ac.in', 'chemistry', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('admin_phy', 'Physics Admin', 'admin_phy@iitb.ac.in', 'physics', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('admin_math', 'Mathematics Admin', 'admin_math@iitb.ac.in', 'mathematics', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('admin_bio', 'Biology Admin', 'admin_bio@iitb.ac.in', 'biology', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample equipment for civil department
INSERT INTO civil (equipment_name, equipment_dept, specification, description, purpose, users, year_of_purchase, mmd_no, supplier, amount, fund, incharge) VALUES 
('Universal Testing Machine', 'civil', 'Capacity: 2000kN, Accuracy: ±1%', 'High precision testing machine for material strength testing', 'Material testing, structural analysis', 'Students, Researchers, Faculty', '2020', 12345, 'ABC Suppliers', 500000.00, 'Department Fund', 'Dr. Smith'),
('Concrete Mixer', 'civil', 'Capacity: 50L, Motor: 2HP', 'Portable concrete mixer for laboratory use', 'Concrete preparation, mixing experiments', 'Students, Lab Technicians', '2019', 12346, 'XYZ Equipment', 25000.00, 'Lab Equipment Fund', 'Prof. Johnson'),
('Survey Equipment Set', 'civil', 'Total Station, Level, Theodolite', 'Complete surveying equipment package', 'Land surveying, construction layout', 'Students, Surveyors', '2021', 12347, 'Survey Solutions', 150000.00, 'Infrastructure Fund', 'Dr. Williams');

-- Insert sample equipment for mechanical department
INSERT INTO mechanical (equipment_name, equipment_dept, specification, description, purpose, users, year_of_purchase, mmd_no, supplier, amount, fund, incharge) VALUES 
('CNC Machine', 'mechanical', '3-axis, Work area: 300x200x100mm', 'Computer numerical control milling machine', 'Precision machining, prototyping', 'Students, Researchers', '2022', 23456, 'CNC Solutions', 800000.00, 'Research Fund', 'Dr. Brown'),
('Lathe Machine', 'mechanical', 'Swing: 400mm, Length: 1000mm', 'Conventional lathe for metal turning', 'Metal turning, manufacturing', 'Students, Technicians', '2018', 23457, 'Machine Tools Ltd', 120000.00, 'Equipment Fund', 'Prof. Davis'),
('3D Printer', 'mechanical', 'Build volume: 200x200x200mm, PLA/ABS', 'Fused deposition modeling 3D printer', 'Rapid prototyping, model making', 'Students, Researchers', '2023', 23458, '3D Print Pro', 45000.00, 'Innovation Fund', 'Dr. Wilson');

-- Insert sample equipment for electrical department
INSERT INTO electrical (equipment_name, equipment_dept, specification, description, purpose, users, year_of_purchase, mmd_no, supplier, amount, fund, incharge) VALUES 
('Oscilloscope', 'electrical', 'Bandwidth: 100MHz, 4 channels', 'Digital storage oscilloscope for signal analysis', 'Circuit analysis, signal measurement', 'Students, Researchers', '2021', 34567, 'ElectroTech', 75000.00, 'Lab Fund', 'Dr. Miller'),
('Function Generator', 'electrical', 'Frequency: 0.1Hz-20MHz, 3 waveforms', 'Programmable function generator', 'Signal generation, testing', 'Students, Lab Staff', '2020', 34568, 'Signal Corp', 25000.00, 'Equipment Fund', 'Prof. Garcia'),
('Power Supply', 'electrical', 'Output: 0-30V, 0-3A, Dual channel', 'Variable DC power supply', 'Circuit powering, testing', 'Students, Technicians', '2019', 34569, 'Power Solutions', 15000.00, 'Basic Equipment', 'Dr. Rodriguez');

-- Insert sample equipment for computer science department
INSERT INTO computer_science (equipment_name, equipment_dept, specification, description, purpose, users, year_of_purchase, mmd_no, supplier, amount, fund, incharge) VALUES 
('High Performance Server', 'computer_science', 'CPU: 32 cores, RAM: 128GB, Storage: 4TB SSD', 'Dedicated server for computational research', 'Data processing, AI/ML training', 'Researchers, Faculty', '2023', 45678, 'Server Solutions', 1200000.00, 'Research Infrastructure', 'Dr. Anderson'),
('GPU Cluster', 'computer_science', '4x RTX 4090, 64GB RAM, 2TB NVMe', 'GPU computing cluster for deep learning', 'Machine learning, neural networks', 'Students, Researchers', '2022', 45679, 'GPU Tech', 800000.00, 'AI Research Fund', 'Prof. Taylor'),
('Network Analyzer', 'computer_science', 'Frequency: 1MHz-6GHz, 2 ports', 'Vector network analyzer for RF testing', 'Network analysis, RF design', 'Students, Engineers', '2021', 45680, 'Network Pro', 95000.00, 'Communication Fund', 'Dr. Martinez');

-- Insert sample equipment for chemistry department
INSERT INTO chemistry (equipment_name, equipment_dept, specification, description, purpose, users, year_of_purchase, mmd_no, supplier, amount, fund, incharge) VALUES 
('HPLC System', 'chemistry', 'Pump: Quaternary, Detector: UV-Vis', 'High performance liquid chromatography system', 'Chemical analysis, compound separation', 'Researchers, Students', '2022', 56789, 'Chromatography Ltd', 450000.00, 'Analytical Fund', 'Dr. Thompson'),
('UV-Vis Spectrophotometer', 'chemistry', 'Wavelength: 190-900nm, Accuracy: ±1nm', 'Ultraviolet-visible spectrophotometer', 'Absorption spectroscopy, concentration measurement', 'Students, Lab Staff', '2021', 56790, 'SpectroTech', 180000.00, 'Lab Equipment', 'Prof. Lee'),
('FTIR Spectrometer', 'chemistry', 'Range: 4000-400cm-1, Resolution: 4cm-1', 'Fourier transform infrared spectrometer', 'Molecular structure analysis, compound identification', 'Researchers, Faculty', '2023', 56791, 'IR Solutions', 350000.00, 'Research Fund', 'Dr. White');

-- Insert sample equipment for physics department
INSERT INTO physics (equipment_name, equipment_dept, specification, description, purpose, users, year_of_purchase, mmd_no, supplier, amount, fund, incharge) VALUES 
('Laser System', 'physics', 'Wavelength: 532nm, Power: 50mW', 'Solid-state laser for optical experiments', 'Optics research, laser physics', 'Students, Researchers', '2022', 67890, 'Laser Tech', 75000.00, 'Optics Fund', 'Dr. Harris'),
('Cryostat', 'physics', 'Temperature: 1.5K-300K, Magnetic field: 9T', 'Low temperature measurement system', 'Cryogenic research, superconductivity', 'Researchers, Faculty', '2021', 67891, 'Cryo Systems', 1200000.00, 'Condensed Matter Fund', 'Prof. Clark'),
('X-ray Diffractometer', 'physics', 'Angle range: -3° to +160°, Resolution: 0.01°', 'X-ray diffraction system for crystal analysis', 'Crystal structure analysis, materials science', 'Students, Researchers', '2023', 67892, 'XRD Solutions', 800000.00, 'Materials Fund', 'Dr. Lewis');

-- Insert sample equipment for mathematics department
INSERT INTO mathematics (equipment_name, equipment_dept, specification, description, purpose, users, year_of_purchase, mmd_no, supplier, amount, fund, incharge) VALUES 
('Computational Cluster', 'mathematics', 'CPU: 64 cores, RAM: 256GB, GPU: 8x V100', 'High-performance computing cluster', 'Numerical analysis, mathematical modeling', 'Researchers, Faculty', '2023', 78901, 'Compute Solutions', 2000000.00, 'Computational Fund', 'Dr. Hall'),
('Visualization Workstation', 'mathematics', 'GPU: RTX 6000, RAM: 64GB, 4K Display', 'High-end visualization system', 'Mathematical visualization, 3D modeling', 'Students, Researchers', '2022', 78902, 'Viz Tech', 150000.00, 'Visualization Fund', 'Prof. Young'),
('Statistical Software License', 'mathematics', 'Multi-user license, 50 seats', 'Advanced statistical analysis software', 'Statistical analysis, data modeling', 'Students, Faculty', '2021', 78903, 'Stat Software', 50000.00, 'Software Fund', 'Dr. King');

-- Insert sample equipment for biology department
INSERT INTO biology (equipment_name, equipment_dept, specification, description, purpose, users, year_of_purchase, mmd_no, supplier, amount, fund, incharge) VALUES 
('Microscope System', 'biology', 'Magnification: 40x-1000x, Digital camera', 'Advanced biological microscope with imaging', 'Cell biology, tissue analysis', 'Students, Researchers', '2022', 89012, 'Micro Tech', 120000.00, 'Biology Fund', 'Dr. Scott'),
('PCR Machine', 'biology', '96 wells, Temperature range: 4-99°C', 'Polymerase chain reaction thermal cycler', 'DNA amplification, genetic analysis', 'Students, Lab Staff', '2021', 89013, 'BioTech Solutions', 85000.00, 'Molecular Fund', 'Prof. Green'),
('Centrifuge', 'biology', 'Speed: 15000 RPM, Capacity: 24 tubes', 'High-speed centrifuge for sample separation', 'Cell separation, protein purification', 'Students, Researchers', '2023', 89014, 'Centrifuge Pro', 45000.00, 'Lab Equipment', 'Dr. Baker'); 