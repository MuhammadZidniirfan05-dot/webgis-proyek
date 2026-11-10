--
-- PostgreSQL database dump
--

\restrict VJnDyY9FJBRBVyhQcdv4NmRQTMWozzrPr12AdDGXFFN1pBdSqBMMpVMb0UUvcyA

-- Dumped from database version 17.11
-- Dumped by pg_dump version 17.11

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: regions; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.regions VALUES (1, 'Wilayah Contoh', 'kecamatan', '2026-11-07 08:26:22', '2026-11-07 08:26:22');


--
-- Data for Name: projects; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.projects VALUES (2, 'PRJ-002', 'Perbaikan Jalan Contoh', NULL, 'berjalan', 500000000.00, '2026-08-01', '2026-10-15', 0, 1, 2, '2026-11-07 08:26:22', '2026-11-07 13:40:46', '0101000020E610000065389ECF80B25A4074B7EBA529D218C0');
INSERT INTO public.projects VALUES (4, 'PRJ-0004', 'APARTEMEN', 'APA', 'berjalan', 1000000000.00, '2026-12-04', '2027-05-04', 12, 1, 1, '2026-11-07 08:56:45', '2026-11-07 14:23:45', '0101000020E61000005C2041F163AF5A406B7EFCA5451D19C0');
INSERT INTO public.projects VALUES (3, 'PRJ-003', 'Gedung kuliah', NULL, 'berjalan', 500000000.00, '2026-11-01', '2027-03-01', 0, 1, 2, '2026-11-07 08:26:22', '2026-11-07 19:41:46', '0101000020E61000006666666666E65A40A4703D0AD7A31BC0');
INSERT INTO public.projects VALUES (1, 'PRJ-001', 'Jembatan Contoh', NULL, 'berjalan', 500000000.00, '2026-10-01', '2026-11-10', 45, 1, 2, '2026-11-07 08:26:22', '2026-11-08 04:22:19', '0101000020E6100000D95F764F1EB65A40849ECDAACFD518C0');


--
-- Data for Name: daily_reports; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.daily_reports VALUES (1, 4, 3, '2026-11-07', 30, NULL, '2026-11-07 14:07:34', '2026-11-07 14:07:34');
INSERT INTO public.daily_reports VALUES (2, 4, 3, '2026-11-07', 12, 'KENDALA MATRIAL', '2026-11-07 14:09:53', '2026-11-07 14:23:45');
INSERT INTO public.daily_reports VALUES (3, 1, 3, '2026-11-08', 45, 'Lewat API', '2026-11-08 04:22:19', '2026-11-08 04:22:19');


--
-- Data for Name: project_user; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.project_user VALUES (1, 1, 3, '2026-11-07 08:26:22', '2026-11-07 08:26:22');
INSERT INTO public.project_user VALUES (2, 2, 3, '2026-11-07 08:26:22', '2026-11-07 08:26:22');
INSERT INTO public.project_user VALUES (3, 3, 3, '2026-11-07 08:26:22', '2026-11-07 08:26:22');


--
-- Data for Name: report_photos; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.report_photos VALUES (2, 2, 'laporan/4/BpCYirtuMzTcBo648qXtlNNyEv96VFD4Ykcl15Ox.png', NULL, '2026-11-07 14:09:53', '2026-11-07 14:09:53');
INSERT INTO public.report_photos VALUES (3, 1, 'laporan/4/coMoUqByuM39DFu0teNeVA5Q6dpgVKd01vSnwJGE.png', NULL, '2026-11-07 15:39:35', '2026-11-07 15:39:35');


--
-- Name: daily_reports_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.daily_reports_id_seq', 3, true);


--
-- Name: project_user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.project_user_id_seq', 4, true);


--
-- Name: projects_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.projects_id_seq', 4, true);


--
-- Name: regions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.regions_id_seq', 1, true);


--
-- Name: report_photos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.report_photos_id_seq', 3, true);


--
-- PostgreSQL database dump complete
--

\unrestrict VJnDyY9FJBRBVyhQcdv4NmRQTMWozzrPr12AdDGXFFN1pBdSqBMMpVMb0UUvcyA

