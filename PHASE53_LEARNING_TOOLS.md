# NACS-Phil Phase 53 - Online Dictionary and Grammar Learning Tools

Phase 53 adds a public Learning Tools page under Resources.

## Dictionary
- manual word lookup only;
- server-side request to a configurable dictionary endpoint;
- successful definitions are cached to reduce third-party traffic;
- input is restricted to short English word forms;
- external failures are shown as a friendly unavailable state.

## Grammar
- manual submission only; no background or automatic text transmission;
- server-side POST to a configurable grammar endpoint;
- defaults to English US/UK;
- conservative NACS maximum length defaults to 2,000 characters;
- result suggestions are advisory;
- submitted grammar text is not stored by NACS-Phil.

The public page explicitly warns users never to paste grades, student records, admissions information, passwords, or other confidential/personal data.

The default public providers are configurable through `.env.example`, so a future Laravel host can switch providers or use a self-hosted grammar service without changing application code.
