-- Create programs table for ECCT website
CREATE TABLE `programs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `featured_image` varchar(500) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  `status` enum('active','upcoming','completed','paused') DEFAULT 'active',
  `objectives` text DEFAULT NULL,
  `activities` text DEFAULT NULL,
  `impact` text DEFAULT NULL,
  `target_audience` varchar(200) DEFAULT NULL,
  `budget` decimal(10,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `coordinator` varchar(100) DEFAULT NULL,
  `partner_organizations` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`),
  KEY `idx_active` (`is_active`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample programs data
INSERT INTO `programs` (`title`, `slug`, `description`, `excerpt`, `category`, `duration`, `location`, `status`, `objectives`, `activities`, `impact`, `is_active`, `is_featured`) VALUES
('Tree Planting Initiative', 'tree-planting-initiative', '<p>Our flagship tree planting program focuses on reforestation and afforestation across Tanzania. We work with local communities to plant indigenous tree species that support biodiversity and provide economic benefits.</p><p>This program includes training local communities on sustainable forestry practices, seedling production, and long-term forest management. We target degraded areas, riverbanks, and community lands to maximize environmental impact.</p>', 'Community-led reforestation program planting indigenous trees across Tanzania to combat deforestation and support local livelihoods.', 'reforestation', '2 years', 'Nationwide Tanzania', 'active', 'Plant 100,000 indigenous trees, restore 500 hectares of degraded land, train 1,000 community members in sustainable forestry practices', 'Tree seedling production, community training workshops, planting events, forest monitoring, sustainable harvesting training', 'Improved forest cover, enhanced biodiversity, increased community income from forest products, climate change mitigation', 1, 1),

('Coastal Conservation Program', 'coastal-conservation-program', '<p>Protecting Tanzania''s beautiful coastline through mangrove restoration, marine protected areas, and sustainable fishing practices. Our coastal program works directly with fishing communities to balance conservation with livelihoods.</p><p>We focus on mangrove restoration, coral reef protection, and establishing community-managed marine reserves. The program includes training fishers in sustainable practices and alternative livelihood opportunities.</p>', 'Comprehensive coastal protection program focusing on mangrove restoration and sustainable marine resource management.', 'marine conservation', '3 years', 'Coastal Tanzania', 'active', 'Restore 200 hectares of mangroves, establish 5 community marine reserves, train 500 fishers in sustainable practices', 'Mangrove planting, coral restoration, fisheries management training, alternative livelihood programs, marine monitoring', 'Restored marine ecosystems, improved fish stocks, diversified community livelihoods, enhanced climate resilience', 1, 1),

('Environmental Education', 'environmental-education', '<p>Empowering the next generation through comprehensive environmental education programs in schools and communities. We develop curricula, train teachers, and engage students in hands-on conservation activities.</p><p>Our education program reaches primary and secondary schools across Tanzania, providing resources, training, and ongoing support to integrate environmental topics into existing curricula.</p>', 'School-based environmental education program building awareness and action among students and teachers.', 'education', '1 year', 'Urban and Rural Schools', 'active', 'Reach 50 schools, train 200 teachers, engage 5,000 students in environmental activities', 'Curriculum development, teacher training, student clubs, environmental camps, tree planting, waste management projects', 'Increased environmental awareness, student-led conservation projects, improved school environments, teacher capacity building', 1, 1),

('Sustainable Agriculture Initiative', 'sustainable-agriculture-initiative', '<p>Supporting smallholder farmers to adopt climate-smart agricultural practices that increase productivity while protecting the environment. This program focuses on organic farming, water conservation, and soil health.</p><p>We provide training, resources, and ongoing support to help farmers transition to sustainable practices that improve yields and protect natural resources.</p>', 'Climate-smart agriculture program helping farmers increase productivity while protecting environmental resources.', 'agriculture', '18 months', 'Rural Tanzania', 'active', 'Train 1,000 farmers in sustainable practices, establish 20 demonstration plots, increase crop yields by 30%', 'Farmer training, demonstration plots, organic farming techniques, water conservation, soil management, market linkages', 'Improved food security, increased farmer incomes, enhanced soil health, reduced chemical inputs, climate adaptation', 1, 0),

('Waste Management Program', 'waste-management-program', '<p>Addressing urban waste challenges through community-based waste management systems, recycling initiatives, and waste reduction education. We work with local governments and communities to develop sustainable solutions.</p><p>The program includes establishing recycling centers, training waste collectors, and implementing waste separation systems at the community level.</p>', 'Community waste management program promoting recycling, waste reduction, and sustainable disposal practices.', 'waste management', '2 years', 'Urban Centers', 'upcoming', 'Establish 10 recycling centers, reduce waste to landfills by 40%, train 500 waste collectors', 'Recycling center setup, waste collector training, community education, waste separation systems, composting programs', 'Reduced environmental pollution, improved urban cleanliness, job creation in recycling sector, community health improvements', 1, 0),

('Wildlife Protection Initiative', 'wildlife-protection-initiative', '<p>Protecting Tanzania''s wildlife through anti-poaching efforts, habitat restoration, and community engagement. We work with local communities to develop sustainable wildlife-based livelihoods.</p><p>This comprehensive program includes wildlife monitoring, habitat protection, community tourism development, and human-wildlife conflict resolution.</p>', 'Wildlife conservation program protecting endangered species through community engagement and habitat restoration.', 'wildlife protection', '3 years', 'National Parks and Reserves', 'active', 'Protect 10 endangered species, restore 1,000 hectares of habitat, establish 5 community conservancies', 'Wildlife monitoring, anti-poaching patrols, habitat restoration, community tourism, human-wildlife conflict mitigation', 'Stable wildlife populations, restored ecosystems, alternative community livelihoods, reduced human-wildlife conflict', 1, 1);