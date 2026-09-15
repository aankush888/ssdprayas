Technical & On-Page SEO Audit Report
1.	/ai-for-school is set to noindex
The page mapped to keywords 8–12 ("AI for schools in India", "AI training for schools", "AI education for schools", "teaching AI in schools", "AI teaching school") carries this meta tag:
meta-robots: noindex, follow
This explicitly tells Google not to include the page in search results. It will not rank for any of the five keywords mapped to it — or for anything else — until this is changed to "index, follow". Once all the changes are done, set the meta robots to index.
2.	Organization Schema Code Placement in the Head Section
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "EducationalOrganization",
  "name": "SSD Prayas",
  "url": "https://ssdprayas.com/",
  "logo": "https://ssdprayas.com/assets/ssdprayaslogo-1.png",
  "sameAs": [
    "https://www.facebook.com/ssdprayas",
    "https://www.instagram.com/ssdprayas/"
  ]
}
</script>
3.	FAQ Schema Code Placement in the Head Section
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [{
    "@type": "Question",
    "name": "What makes SSD Prayas the best AI learning platform in India for schools?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "Most platforms hand a school a login and call it done. We don't. SSD Prayas trains your own teachers, delivers the curriculum inside your existing classroom setup, and certifies both students and staff — so the learning doesn't disappear the moment a subscription ends. That combination of hands-on delivery, teacher independence, and NEP 2020 alignment is what schools tell us sets us apart from platforms that are really just video libraries with a login screen."
    }
  },{
    "@type": "Question",
    "name": "Is there an AI course for beginners in India, or do students need coding experience first?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "No coding background is needed to start. Our beginner-level modules are built for students who've never written a line of code — the early classes focus on how AI actually works in plain language, with simple hands-on tools, before anything resembling \"programming\" shows up. Coding gets introduced gradually as students move into higher grades or more advanced tracks, not on day one."
    }
  },{
    "@type": "Question",
    "name": "Do you offer online AI classes for students, or is everything in-person?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "Both, depending on what a school needs. Some partner schools run sessions fully offline in their existing computer labs; others prefer online delivery, and a fair number end up doing a mix of the two. The curriculum itself doesn't change based on format — what changes is how it's delivered, and we build that around the school's infrastructure rather than forcing one model on everyone."
    }
  },{
    "@type": "Question",
    "name": "What does the AI certification actually cover, and is it recognised outside SSD Prayas?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "Certification is tied to completed, assessed work — not attendance. Students and educators go through an assessment and verification check before any certificate is issued, so it reflects what was actually learned, not just a completed calendar. For working professionals in particular, the certificate is designed to be something you can genuinely point to in a resume or interview, not a participation trophy."
    }
  },{
    "@type": "Question",
    "name": "Can working professionals join, or is this only for schools and students?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "Professionals are one of our three main tracks, alongside students and educators. The professional track is applied rather than academic — real tools, real workflows, project-based learning — aimed at people upskilling for their current job or pivoting into AI-adjacent roles, not at people looking for a theory-heavy refresher course."
    }
  }]
}
</script>
4.	Alt Texts
URL: 1
https://ssdprayas.com/assets/img/home-hero.png?v=hd
Alt Text:
Best AI Learning Platform in India – SSD Prayas
URL: 2
https://ssdprayas.com/assets/img/card-students.jpg
Alt Text:
AI training for Schools by SSD Prayas
URL: 3
https://ssdprayas.com/assets/img/card-educators.jpg
Alt Text:
AI Training for Educators by SSD Prayas
URL: 4
https://ssdprayas.com/assets/img/card-professionals.jpg
Alt Text:
AI Training for Professionals
URL: 5
https://ssdprayas.com/assets/img/card-everyone.jpg
Alt Text:
AI Training for Everyone
5.	Meta Tags
URL: 1
https://ssdprayas.com/
Meta Title:
AI Education Company India – AI Courses & Certification – SSD Prayas
Meta Description:
SSD Prayas — India's AI education company. Online AI courses, certification & beginner programmes for students, educators & professionals.
Meta Keywords:
ai education company, best ai learning platform india, ai for education, ai education india, ai certification, online ai classes for students, ai course for beginners india, ssd prayas, nep 2020 ai education, ai skilling india
URL: 2
https://ssdprayas.com/ai-for-school
Meta Title:
AI for Schools in India – AI Training – SSD Prayas
Meta Description:
SSD Prayas delivers AI education for schools across India — training educators, teaching AI in schools with a NEP 2020 curriculum and certification.
Meta Keywords:
ai for schools in india, ai training for schools, ai education for schools, teaching ai in schools, ai teaching school, nep 2020 ai curriculum, ai for school programme, school ai certification
URL: 3
https://ssdprayas.com/programmes
Meta Title:
AI Programmes for Students, Educators & Professionals | SSD Prayas
Meta Description:
Grade-wise AI curriculum for Class 3–12, L1/L2 educator training, and applied AI upskilling for working professionals — online or offline.
URL: 4
https://ssdprayas.com/government
Meta Title:
Government AI Skilling Projects Across India | SSD Prayas
Meta Description:
State-scale AI skilling for government departments — partner onboarding, L1/L2 educator batches, student enrolment and certification tracking.
URL: 5
https://ssdprayas.com/about
Meta Title:
About SSD Prayas – Our AI Education Mission
Meta Description:
SSD Prayas brings practical AI education to students, educators and professionals across India — online, offline and at government scale.
URL: 6
https://ssdprayas.com/careers
Meta Title:
Careers at SSD Prayas – Hiring AI Educators
Meta Description:
Join SSD Prayas as an AI Educator. Hybrid roles across Indian states, training students and teachers in practical AI. Apply with your resume.
URL: 7
https://ssdprayas.com/blogs
Meta Title:
SSD Prayas Blog – AI Education Insights & Stories
Meta Description:
Stories and insights on AI education, NEP 2020 and educator training — what's really working inside Indian classrooms.
URL: 7
https://ssdprayas.com/contact
Meta Title:
Contact SSD Prayas – Start Your AI Journey
Meta Description:
Talk to SSD Prayas about bringing AI education to your school or organisation. Call, WhatsApp or send an enquiry — we respond within 24 hours.
6.	Site-wide 301 Redirection
Both https://www.ssdprayas.com/ and https://ssdprayas.com/ currently serve identical content without a server-level 301 redirect between them, which risks Google indexing both hostnames as separate, duplicate URLs and splitting ranking signals between them; a permanent 301 redirect should be implemented to send all traffic to the single canonical destination, https://ssdprayas.com/.
