<?php
/**
 * Mental Models Knowledge Base
 * Decision-making frameworks used by elite CTOs, founders, and product leaders.
 * Injected into AI prompts to improve reasoning quality — not just knowledge retrieval.
 * Level 5+: Meta-cognitive reasoning — "Think better, not just know more."
 */
return [

    'principle' => 'Mental models are not rules — they are lenses. Apply the right lens to each decision.',

    // ── Core Mental Models ─────────────────────────────────────────────────────
    'models' => [

        'first_principles' => [
            'name'      => 'First Principles Thinking',
            'question'  => 'What is fundamentally true about this problem? What can I not question further?',
            'use_when'  => 'Existing solutions seem wrong, too expensive, or copied without thinking',
            'software_example' => 'Instead of "how does Shopify do checkout?", ask "what does a buyer fundamentally need to complete a purchase?" — reduces to: select product → confirm price → pay → receive confirmation',
            'anti_pattern'     => 'Following conventions blindly because "that\'s how everyone does it"',
        ],

        'inversion' => [
            'name'      => 'Inversion',
            'question'  => 'What would guarantee failure? What would make users hate this? Now avoid those things.',
            'use_when'  => 'Designing for reliability, security, user experience, or product-market fit',
            'software_example' => 'Instead of "how to make good UX?" ask "what makes UX terrible?" → confusion, slow pages, too many clicks, no feedback. Eliminating those is often easier than optimizing.',
            'anti_pattern'     => 'Only asking what to ADD — never asking what to REMOVE',
        ],

        'second_order_thinking' => [
            'name'      => 'Second-Order Thinking',
            'question'  => 'And then what? What happens after the obvious consequence?',
            'use_when'  => 'Architecture decisions, pricing changes, feature additions, policy changes',
            'software_example' => 'Adding more features → seems good (more value). Second order: users confused → churn increases → support costs rise. Third order: reputation damaged. Often simplifying is better.',
            'anti_pattern'     => 'Thinking only one step ahead',
        ],

        'opportunity_cost' => [
            'name'      => 'Opportunity Cost',
            'question'  => 'What am I NOT doing by doing this? What is the best alternative use of this resource?',
            'use_when'  => 'Roadmap prioritization, hiring decisions, build vs buy, feature development',
            'software_example' => 'Building advanced reporting takes 3 weeks. The opportunity cost: those 3 weeks could be spent on onboarding improvement (which has 3x higher impact on retention). The report has 5% user usage.',
            'anti_pattern'     => 'Measuring only the cost/benefit of what you ARE building, not what you COULD be building',
        ],

        'leverage' => [
            'name'      => 'Leverage Point Thinking',
            'question'  => 'Where is the smallest change that creates the largest impact?',
            'use_when'  => 'Performance optimization, conversion improvement, cost reduction',
            'software_example' => 'Adding Redis caching to one hot query (5 minutes of work) reduces server load 40%. This is 100x more leverage than adding a new feature.',
            'anti_pattern'     => 'Optimizing things that don\'t matter while missing the 80/20 leverage point',
        ],

        'compounding' => [
            'name'      => 'Compounding Effects',
            'question'  => 'Will this get better over time on its own? How do I create compounding loops?',
            'use_when'  => 'Platform design, knowledge systems, ecosystem building, caching strategies',
            'software_example' => 'A decision cache that stores project outcomes compounds — each new project makes future projects faster and cheaper. Building features that users TELL others about compounds distribution.',
            'anti_pattern'     => 'Linear thinking: "this feature will bring X users" — ignoring network and flywheel effects',
        ],

        'flywheel' => [
            'name'      => 'Flywheel Effect',
            'question'  => 'What reinforcing loop, if started, would keep accelerating with less effort over time?',
            'use_when'  => 'Platform strategy, marketplace design, community building',
            'software_example' => 'More developers → more modules → more customers → more revenue → more developers. The flywheel starts slow but becomes unstoppable. Amazon Web Services is a flywheel; so is WordPress plugins.',
            'anti_pattern'     => 'Building without identifying the core flywheel mechanism',
        ],

        'minimum_viable_knowledge' => [
            'name'      => 'Minimum Viable Knowledge',
            'question'  => 'What is the minimum I need to know to make a good decision? What can I learn by doing?',
            'use_when'  => 'MVP planning, feature prioritization, technology selection',
            'software_example' => 'Instead of researching every payment gateway for 2 weeks, build with Stripe, ship, then switch if needed. The cost of switching is lower than the cost of delayed launch.',
            'anti_pattern'     => 'Analysis paralysis — researching to eliminate ALL uncertainty before acting',
        ],

        'reversibility' => [
            'name'      => 'Reversibility Test (Type 1 vs Type 2 Decisions)',
            'question'  => 'Is this a one-way door or a two-way door? Irreversible decisions need more thought.',
            'use_when'  => 'Architecture choices, database design, API contracts, public API versioning',
            'software_example' => 'Choosing your database schema is a one-way door (painful to change). Choosing your color scheme is a two-way door (easy to change). Spend 10x more time on one-way door decisions.',
            'anti_pattern'     => 'Treating all decisions with equal urgency — fast on irreversible, slow on trivial',
        ],

        'map_vs_territory' => [
            'name'      => 'Map vs Territory',
            'question'  => 'Is my model of reality accurate? Am I optimizing for the model or for actual outcomes?',
            'use_when'  => 'Testing strategy, analytics, metrics selection, user research',
            'software_example' => 'A test suite that passes 100% is the MAP. Production users experiencing bugs is the TERRITORY. Tests are only useful if they reflect real usage. Mock-heavy tests are a map that doesn\'t match the territory.',
            'anti_pattern'     => 'Gaming metrics instead of improving underlying reality',
        ],

        'pareto_principle' => [
            'name'      => 'Pareto Principle (80/20)',
            'question'  => 'Which 20% of inputs produce 80% of the output? Where is the leverage?',
            'use_when'  => 'Bug fixing, performance optimization, feature prioritization, testing strategy',
            'software_example' => 'In most apps: 20% of features are used by 80% of users. 20% of bugs cause 80% of support tickets. 20% of DB queries cause 80% of performance problems. Fix THOSE first.',
            'anti_pattern'     => 'Treating everything equally when inputs vary wildly in impact',
        ],

        'occams_razor' => [
            'name'      => 'Occam\'s Razor',
            'question'  => 'What is the simplest explanation or solution that works?',
            'use_when'  => 'Architecture design, debugging, feature design, API design',
            'software_example' => 'Before adding microservices, event sourcing, CQRS, and a message broker — ask: can a monolith with queues solve this? Usually yes. Prefer the simplest architecture that handles the required scale.',
            'anti_pattern'     => 'Over-engineering — adding complexity before it\'s needed',
        ],

        'constraint_thinking' => [
            'name'      => 'Theory of Constraints',
            'question'  => 'What is the single biggest constraint? Fixing anything else is waste until THAT is fixed.',
            'use_when'  => 'Performance bottlenecks, team productivity, scaling problems',
            'software_example' => 'If your bottleneck is slow database queries, adding more server RAM is waste. Fix the DB query first. Then re-evaluate the constraint — it will have moved.',
            'anti_pattern'     => 'Optimizing non-constraints while the actual bottleneck remains unaddressed',
        ],

        'regret_minimization' => [
            'name'      => 'Regret Minimization Framework',
            'question'  => 'When I am 80 years old looking back, will I regret not trying this?',
            'use_when'  => 'Strategic product bets, entering new markets, pivots',
            'software_example' => 'Jeff Bezos used this to quit his banking job and start Amazon. For products: "Will we regret not building the AI intelligence layer before competitors do?" — often clarifies long-term priorities.',
            'anti_pattern'     => 'Short-term risk aversion that leads to long-term strategic regret',
        ],
    ],

    // ── Application to Software Decisions ─────────────────────────────────────
    'decision_matrix_pairing' => [
        'architecture_choice'      => 'Use: first_principles + reversibility + occams_razor',
        'feature_prioritization'   => 'Use: opportunity_cost + pareto_principle + leverage',
        'platform_strategy'        => 'Use: flywheel + compounding + second_order_thinking',
        'technical_debt_decision'  => 'Use: constraint_thinking + leverage + second_order_thinking',
        'build_vs_buy_decision'    => 'Use: opportunity_cost + minimum_viable_knowledge + reversibility',
        'api_design'               => 'Use: reversibility + occams_razor + map_vs_territory',
        'mvp_scoping'              => 'Use: minimum_viable_knowledge + pareto_principle + inversion',
        'scaling_decision'         => 'Use: constraint_thinking + second_order_thinking + leverage',
    ],

];
