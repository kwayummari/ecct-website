-- ECCT Partners and Team Database Setup
-- Run this script to create the necessary tables and sample data

-- Create Partners Table
CREATE TABLE IF NOT EXISTS partners (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    logo_path VARCHAR(255) NOT NULL,
    website_url VARCHAR(255),
    partnership_type ENUM('sponsor', 'implementation', 'technical', 'funding', 'other') DEFAULT 'sponsor',
    sort_order INT(11) DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_partnership_type (partnership_type),
    INDEX idx_featured (is_featured),
    INDEX idx_active (is_active),
    INDEX idx_sort_order (sort_order)
);

-- Create Team Table
CREATE TABLE IF NOT EXISTS team_members (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    position VARCHAR(200) NOT NULL,
    bio TEXT,
    image_path VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50),
    linkedin_url VARCHAR(255),
    twitter_url VARCHAR(255),
    facebook_url VARCHAR(255),
    department ENUM('management', 'technical', 'finance', 'operations', 'communications', 'field', 'other') DEFAULT 'management',
    sort_order INT(11) DEFAULT 0,
    is_leadership TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_department (department),
    INDEX idx_leadership (is_leadership),
    INDEX idx_active (is_active),
    INDEX idx_sort_order (sort_order)
);

-- Insert Sample Partners Data
INSERT INTO partners (name, description, logo_path, website_url, partnership_type, sort_order, is_featured, is_active) VALUES
('UN Environment Programme', 'Global environmental authority providing leadership and encouraging partnership in caring for the environment.', 'assets/images/partners/unep-logo.png', 'https://www.unep.org', 'funding', 1, 1, 1),
('Tanzania Forest Conservation Group', 'Working to enhance conservation and sustainable management of forests for poverty alleviation.', 'assets/images/partners/tfcg-logo.png', 'https://www.tfcg.org', 'implementation', 2, 1, 1),
('Green Climate Fund', 'Supporting developing countries to limit or reduce their greenhouse gas emissions.', 'assets/images/partners/gcf-logo.png', 'https://www.greenclimate.fund', 'funding', 3, 1, 1),
('WWF Tanzania', 'Leading conservation organization working to protect wildlife and natural habitats.', 'assets/images/partners/wwf-logo.png', 'https://www.wwf.org.tz', 'implementation', 4, 1, 1),
('USAID', 'Leading American government agency providing development and humanitarian assistance.', 'assets/images/partners/usaid-logo.png', 'https://www.usaid.gov', 'funding', 5, 1, 1),
('Sokoine University of Agriculture', 'Leading agricultural university providing research and technical support.', 'assets/images/partners/sua-logo.png', 'https://www.sua.ac.tz', 'technical', 6, 0, 1);

-- Insert Sample Team Members Data
INSERT INTO team_members (name, position, bio, image_path, email, department, sort_order, is_leadership, is_active) VALUES
('Dr. Sarah Mwangi', 'Executive Director', 'Environmental scientist with over 15 years of experience in conservation and community development. PhD in Environmental Science from University of Dar es Salaam.', 'assets/images/team/sarah-mwangi.jpg', 'sarah@ecct.org', 'management', 1, 1, 1),
('James Kilimanjaro', 'Program Director', 'Experienced program manager specializing in community-based conservation initiatives. Masters in Development Studies.', 'assets/images/team/james-kilimanjaro.jpg', 'james@ecct.org', 'management', 2, 1, 1),
('Dr. Grace Mtembei', 'Research Coordinator', 'Environmental researcher with expertise in climate change adaptation and mitigation strategies. PhD in Climate Science.', 'assets/images/team/grace-mtembei.jpg', 'grace@ecct.org', 'technical', 3, 1, 1),
('Peter Kimani', 'Field Operations Manager', 'Community mobilization expert with extensive experience in rural development and environmental conservation.', 'assets/images/team/peter-kimani.jpg', 'peter@ecct.org', 'operations', 4, 1, 1),
('Mary Josephine', 'Finance Manager', 'Certified Public Accountant with experience in NGO financial management and donor compliance.', 'assets/images/team/mary-josephine.jpg', 'mary@ecct.org', 'finance', 5, 1, 1),
('David Moshi', 'Communications Officer', 'Communications specialist with background in digital marketing and environmental advocacy.', 'assets/images/team/david-moshi.jpg', 'david@ecct.org', 'communications', 6, 0, 1),
('Elizabeth Nguvu', 'Community Outreach Coordinator', 'Community development specialist working directly with local communities on conservation initiatives.', 'assets/images/team/elizabeth-nguvu.jpg', 'elizabeth@ecct.org', 'field', 7, 0, 1),
('Francis Kilolo', 'Technical Assistant', 'Environmental technician supporting field activities and data collection for conservation projects.', 'assets/images/team/francis-kilolo.jpg', 'francis@ecct.org', 'technical', 8, 0, 1);